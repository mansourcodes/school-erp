<?php

namespace App\Http\Controllers\Admin;

use App\Events\MessageSent;
use App\Http\Requests\MessageRequest;
use App\Models\ClassRoom;
use App\Models\Message;
use App\Models\MessageRecipient;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Superadmin view of every portal message (PLAN.md §5.11): read any message with its read
 * progress, send one to a classroom's students, fix a message's text, or delete it (soft: it
 * leaves every inbox at once, like a teacher's delete).
 *
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class MessageCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation { store as traitStore; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(Message::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/message');
        CRUD::setEntityNameStrings(trans('message.message'), trans('message.messages'));

        // Teachers never reach /admin pages beyond attendance (RedirectTeacherToAttendance); this
        // keeps the CRUD Superadmin-only even if that list changes.
        if (! backpack_user()->hasRole('Superadmin')) {
            CRUD::denyAccess(['list', 'create', 'update', 'delete', 'show']);
        }

        CRUD::orderBy('id', 'desc');
    }

    protected function setupListOperation()
    {
        CRUD::addClause('with', ['sender', 'classroom']);
        CRUD::addClause('withCount', ['recipients', 'recipients as read_count' => function ($q) {
            $q->whereNotNull('read_at');
        }]);

        CRUD::column('id');
        $this->senderColumn();
        $this->classroomColumn();
        CRUD::addColumn([
            'name' => 'body',
            'label' => trans('message.body'),
            'type' => 'text',
            'limit' => 80,
        ]);
        CRUD::addColumn([
            'name' => 'read_progress',
            'label' => trans('message.read_progress'),
            'type' => 'closure',
            'escaped' => true,
            'function' => function (Message $message) {
                return $message->read_count . ' / ' . $message->recipients_count;
            },
        ]);
        CRUD::addColumn(['name' => 'created_at', 'label' => trans('message.sent_at'), 'type' => 'datetime']);
    }

    protected function setupShowOperation()
    {
        CRUD::set('show.setFromDb', false);

        CRUD::column('id');
        $this->senderColumn();
        $this->classroomColumn();
        CRUD::addColumn(['name' => 'body', 'label' => trans('message.body'), 'type' => 'textarea']);
        CRUD::addColumn(['name' => 'created_at', 'label' => trans('message.sent_at'), 'type' => 'datetime']);
        CRUD::addColumn(['name' => 'updated_at', 'label' => trans('message.updated_at'), 'type' => 'datetime']);
        CRUD::addColumn([
            'name' => 'recipients_list',
            'label' => trans('message.recipients'),
            'type' => 'closure',
            'function' => function (Message $message) {
                $rows = $message->recipients()->with('student')->get()
                    ->sortBy(function (MessageRecipient $r) {
                        return optional($r->student)->name;
                    })
                    ->map(function (MessageRecipient $r) {
                        $state = $r->read_at
                            ? '<span class="badge badge-success">' . e(trans('message.read')) . ' ' . e($r->read_at->format('Y-m-d H:i')) . '</span>'
                            : '<span class="badge badge-secondary">' . e(trans('message.unread')) . '</span>';

                        return '<li>' . e(optional($r->student)->name ?? ('#' . $r->student_id)) . ' ' . $state . '</li>';
                    });

                return '<ul class="mb-0 pl-3 pr-3">' . $rows->implode('') . '</ul>';
            },
        ]);
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(MessageRequest::class);

        CRUD::addField([
            'label' => trans('classroom.classroom'),
            'type' => 'select2_from_ajax',
            'name' => 'class_room_id',
            'entity' => 'classroom',
            'model' => ClassRoom::class,
            'attribute' => 'first_long_name',
            'data_source' => url('api/classroom'),
            'placeholder' => trans('message.choose_classroom'),
            'minimum_input_length' => 1,
        ]);
        CRUD::addField([
            'name' => 'all_students',
            'label' => trans('message.all_students'),
            'type' => 'checkbox',
        ]);
        CRUD::addField([
            'label' => trans('message.recipients'),
            'type' => 'select2_from_ajax_multiple',
            'name' => 'student_ids',
            'model' => \App\Models\Student::class,
            'attribute' => 'long_name',
            'data_source' => url('api/student'),
            'placeholder' => trans('message.choose_students'),
            'minimum_input_length' => 0,
            // The search endpoint narrows to the chosen classroom's students.
            'dependencies' => ['class_room_id'],
            'include_all_form_fields' => true,
            'hint' => trans('message.recipients_hint'),
        ]);
        $this->bodyField();
    }

    /** Only the text can change: the recipients and their read state stay as sent. */
    protected function setupUpdateOperation()
    {
        CRUD::setValidation(MessageRequest::class);
        $this->bodyField();
    }

    /** Like a teacher's send (MessageController::send), with the admin as sender; pushes go out now. */
    public function store()
    {
        $this->crud->hasAccessOrFail('create');
        $request = $this->crud->validateRequest();

        $classroom = ClassRoom::with('students')->findOrFail($request->input('class_room_id'));
        $studentIds = $request->boolean('all_students')
            ? $classroom->students->pluck('id')->all()
            : array_values(array_unique(array_map('intval', $request->input('student_ids', []))));

        $message = DB::transaction(function () use ($classroom, $studentIds, $request) {
            $message = Message::create([
                'sender_user_id' => backpack_user()->id,
                'class_room_id' => $classroom->id,
                'body' => trim($request->input('body')),
            ]);
            $message->recipients()->createMany(array_map(function ($id) {
                return ['student_id' => $id];
            }, $studentIds));

            return $message;
        });

        event(new MessageSent($message, true));

        \Alert::success(trans('backpack::crud.insert_success'))->flash();
        $this->crud->setSaveAction();

        return $this->crud->performSaveAction($message->getKey());
    }

    protected function bodyField()
    {
        CRUD::addField([
            'name' => 'body',
            'label' => trans('message.body'),
            'type' => 'textarea',
            'attributes' => ['maxlength' => Message::MAX_LENGTH, 'rows' => 5],
        ]);
    }

    protected function senderColumn()
    {
        CRUD::addColumn([
            'name' => 'sender',
            'label' => trans('message.sender'),
            'type' => 'relationship',
            'attribute' => 'name',
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhereHas('sender', function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%');
                })->orWhere('body', 'like', '%' . $searchTerm . '%');
            },
        ]);
    }

    protected function classroomColumn()
    {
        CRUD::addColumn([
            'name' => 'classroom_label',
            'label' => trans('classroom.classroom'),
            'type' => 'closure',
            'escaped' => true,
            'function' => function (Message $message) {
                return optional($message->classroom)->first_long_name ?? ('#' . $message->class_room_id);
            },
        ]);
    }
}
