<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\PaymentExemptionRequest;
use App\Models\Account\PaymentExemption;
use App\Models\Course;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Exemption tickets: a student does not pay for one course (Accounting → الإعفاء من الدفع).
 * The account reports read these instead of `students.financial_support_status`.
 *
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class PaymentExemptionCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation { store as traitStore; }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup()
    {
        CRUD::setModel(PaymentExemption::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/paymentexemption');
        CRUD::setEntityNameStrings(trans('account.exemption'), trans('account.exemptions'));
    }

    protected function setupListOperation()
    {
        $this->crud->enableExportButtons();
        $this->crud->with(['student', 'course.academicPath', 'creator']);

        CRUD::addColumn([
            'name' => 'id',
            'label' => trans('account.exemption_ticket'),
        ]);

        CRUD::addColumn([
            'name' => 'student_id',
            'label' => trans('student.name'),
            'type' => 'relationship',
            'attribute' => 'long_name',
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhereHas('student', function ($query) use ($searchTerm) {
                    $query->where('name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('mobile', 'like', '%' . $searchTerm . '%')
                        ->orWhere('cpr', 'like', '%' . $searchTerm . '%');
                });
            },
        ]);

        CRUD::addColumn([
            'name' => 'course_id',
            'label' => trans('course.course'),
            'type' => 'relationship',
            'attribute' => 'long_name',
        ]);

        CRUD::addColumn([
            'name' => 'reason',
            'label' => trans('account.exemption_reason'),
            'type' => 'select_from_array',
            'options' => PaymentExemption::reasonOptions(),
        ]);

        CRUD::addColumn([
            'name' => 'has_payment',
            'label' => trans('account.exemption_has_payment'),
            'type' => 'closure',
            'function' => function ($entry) {
                return $entry->hasPayment() ? trans('account.yes') : trans('account.no');
            },
        ]);

        CRUD::addColumn([
            'name' => 'notes',
            'label' => trans('account.exemption_notes'),
            'limit' => 60,
        ]);

        CRUD::addColumn([
            'name' => 'created_by',
            'label' => trans('account.exemption_created_by'),
            'type' => 'relationship',
            'entity' => 'creator',
            'attribute' => 'name',
        ]);

        CRUD::addColumn([
            'name' => 'created_at',
            'label' => trans('account.exemption_created_at'),
            'type' => 'datetime',
        ]);

        $this->crud->addFilter([
            'name' => 'course_id',
            'type' => 'select2',
            'label' => trans('course.course'),
        ], function () {
            return Course::with('academicPath')->orderByDesc('start_date')->get()->pluck('long_name', 'id')->toArray();
        }, function ($value) {
            $this->crud->addClause('where', 'course_id', $value);
        });

        $this->crud->addFilter([
            'name' => 'reason',
            'type' => 'select2_multiple',
            'label' => trans('account.exemption_reason'),
        ], function () {
            return PaymentExemption::reasonOptions();
        }, function ($values) {
            $this->crud->addClause('whereIn', 'reason', json_decode($values));
        });
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation(PaymentExemptionRequest::class);

        CRUD::addField([
            'label' => trans('student.student'),
            'type' => 'select2_from_ajax',
            'name' => 'student_id',
            'entity' => 'student',
            'attribute' => 'long_name',
            'data_source' => backpack_url('api/student'),
            'placeholder' => trans('student.student'),
            'minimum_input_length' => 1,
            'wrapper' => ['class' => 'form-group col-6'],
            'default' => request()->student,
        ]);

        CRUD::addField([
            'label' => trans('course.course'),
            'type' => 'select2_from_ajax',
            'name' => 'course_id',
            'entity' => 'course',
            'attribute' => 'long_name',
            'data_source' => backpack_url('api/course'),
            'placeholder' => trans('course.course'),
            'minimum_input_length' => 1,
            'wrapper' => ['class' => 'form-group col-6'],
            'default' => request()->course,
        ]);

        CRUD::addField([
            'name' => 'reason',
            'label' => trans('account.exemption_reason'),
            'type' => 'select_from_array',
            'options' => PaymentExemption::reasonOptions(),
            'allows_null' => false,
            'wrapper' => ['class' => 'form-group col-12'],
        ]);

        CRUD::addField([
            'name' => 'notes',
            'label' => trans('account.exemption_notes'),
            'type' => 'textarea',
        ]);
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    /** Records who opened the ticket. */
    public function store()
    {
        $this->crud->addField(['type' => 'hidden', 'name' => 'created_by']);
        $this->crud->getRequest()->request->add(['created_by' => backpack_user()->id]);

        return $this->traitStore();
    }
}
