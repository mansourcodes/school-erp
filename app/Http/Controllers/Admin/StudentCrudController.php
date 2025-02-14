<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\StudentRequest;
use App\Models\Account\Payment;
use App\Models\Student;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;


/**
 * Class StudentCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class StudentCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation {
        store as traitStore;
    }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation {
        update as traitUpdate;
    }
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Student::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/student');
        CRUD::setEntityNameStrings(trans('student.student'), trans('student.students'));
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        $this->crud->enableExportButtons();





        CRUD::addColumn([
            'name' => 'student_id',
            'label' => '#',
        ]);

        CRUD::addColumn([
            'name' => 'name',
            'label' => trans('student.name'),
        ]);

        CRUD::addColumn([
            'name' => 'cpr',
            'label' => trans('student.cpr'),
        ]);

        CRUD::addColumn([
            'name' => 'mobile',
            'label' => trans('student.mobile'),
        ]);

        CRUD::addColumn([
            'name' => 'age',
            'label' => trans('student.age'),
        ]);

        // CRUD::addColumn([
        //     'name' => 'email',
        //     'label' => trans('student.email'),
        // ]);


        // CRUD::addColumn([
        //     'name' => 'created_at',
        //     'label' => trans('base.created_at'),
        // ]);

        // CRUD::addColumn([
        //     'name' => 'deleted_at',
        //     'label' => trans('base.deleted_at'),
        // ]);

        // CRUD::addColumn([
        //     'name' => 'updated_at',
        //     'label' => trans('base.updated_at'),
        // ]);

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
         */

        $this->crud->addButtonFromModelFunction('line', 'get_courses_dropdown', 'getCoursesDropdown', 'end');
        $this->crud->addButtonFromModelFunction('line', 'get_unpaid_payments_dropdown', 'getUnpaidPaymentsDropdown', 'end');
        $this->filters();
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(StudentRequest::class);


        CRUD::addField([
            'name' => 'name',
            'label' => trans('student.name'),
        ]);
        CRUD::addField([
            'name' => 'cpr',
            'label' => trans('student.cpr'),
            'type' => 'text',
            'wrapper'   => [
                'class'      => 'form-group col-md-6'
            ],
        ]);
        CRUD::addField([
            'name' => 'email',
            'label' => trans('student.email'),
            'wrapper'   => [
                'class'      => 'form-group col-md-6'

            ],
            'attributes' => [
                'autocomplete' => 'off'
            ]
        ]);


        CRUD::addField([
            'name' => 'mobile',
            'label' => trans('student.mobile'),
            'wrapper'   => [
                'class'      => 'form-group col-md-6'
            ],
        ]);
        CRUD::addField([
            'name' => 'mobile2',
            'label' => trans('student.mobile2'),
            'wrapper'   => [
                'class'      => 'form-group col-md-6'
            ],
        ]);


        CRUD::addField([
            'name' => 'dob',
            'label' => trans('student.dob'),
            'type' => 'date_picker',
            'date_picker_options' => [
                'todayBtn' => 'linked',
                // 'format'   => 'dd-mm-yyyy',
                'language' => 'ar'
            ],
            'wrapper'   => [
                'class'      => 'form-group col-md-6'
            ],
        ]);


        CRUD::addField([
            'name' => 'registration_at',
            'label' => trans('student.registration_at'),
            'type' => 'date_picker',
            'default' => Carbon::now()->format('Y-m-d'),
            'date_picker_options' => [
                'todayBtn' => 'linked',
                // 'format'   => 'dd-mm-yyyy',
                'language' => 'ar',
            ],
            'wrapper'   => [
                'class'      => 'form-group col-md-6'
            ],
        ]);


        CRUD::addField([
            'name' => 'parents_state',
            'label' => trans('student.parents_state'),
            'type' => 'select_from_array',
            'wrapper'   => [
                'class'      => 'form-group col-md-6'
            ],
            'options' => [
                "BOTH" => trans('student.parents_state_options.BOTH'),
                "MOTHER" => trans('student.parents_state_options.MOTHER'),
                "FATHER" => trans('student.parents_state_options.FATHER'),
                "NONE" => trans('student.parents_state_options.NONE')
            ],
            'allows_null' => false,
            'tab'   => trans('student.relationship_state'),
            // 'default' => 'one',
            // 'allows_multiple' => true, // OPTIONAL; needs you to cast this to array in your model;
        ]);
        CRUD::addField([
            'name' => 'relationship_state',
            'label' => trans('student.relationship_state'),
            'type' => 'select_from_array',
            'wrapper'   => [
                'class'      => 'form-group col-md-6'
            ],
            'options' => [
                "UNKNOWN" => trans('student.relationship_state_options.UNKNOWN'),
                "SINGLE" => trans('student.relationship_state_options.SINGLE'),
                "MARRIED" => trans('student.relationship_state_options.MARRIED')
            ],
            'allows_null' => false,
            'tab'   => trans('student.relationship_state'),
            // 'default' => 'one',
            // 'allows_multiple' => true, // OPTIONAL; needs you to cast this to array in your model;
        ]);
        // CRUD::addField([
        //     'name' => 'relationship_state',
        //     'label' => trans('student.relationship_state'),
        //     'type' => 'enum',
        //     'wrapper'   => [
        //         'class'      => 'form-group col-md-6'
        //     ],
        //     'tab'   => trans('student.relationship_state'),
        // ]);
        CRUD::addField([
            'name' => 'family_depends',
            'label' => trans('student.family_depends'),
            'wrapper'   => [
                'class'      => 'form-group col-md-6'
            ],
            'tab'   => trans('student.relationship_state'),
        ]);
        CRUD::addField([
            'name' => 'family_members',
            'label' => trans('student.family_members'),
            'wrapper'   => [
                'class'      => 'form-group col-md-6'
            ],
            'tab'   => trans('student.relationship_state'),
        ]);

        CRUD::addField([
            'name' => 'financial_support_status',
            'label' => trans('student.financial_support_status'),
            'type' => 'select_from_array',
            'wrapper'   => [
                'class'      => 'form-group col-md-12'
            ],
            'options' => [
                "UNKNOWN" => trans('student.financial_support_status_options.UNKNOWN'),
                "FINANCIAL_ISSUE" => trans('student.financial_support_status_options.FINANCIAL_ISSUE'),
                "PARENTS_ISSUE" => trans('student.financial_support_status_options.PARENTS_ISSUE'),
                "TEACHER_SONS" => trans('student.financial_support_status_options.TEACHER_SONS'),
            ],
            'allows_null' => false,
            'tab'   => trans('student.financial_state'),
            // 'default' => 'one',
            // 'allows_multiple' => true, // OPTIONAL; needs you to cast this to array in your model;
        ]);

        CRUD::addField([
            'name' => 'live_in_state',
            'label' => trans('student.live_in_state'),
            'type' => 'select_from_array',
            'wrapper'   => [
                'class'      => 'form-group col-md-6'
            ],
            'options' => [
                "UNKNOWN" => trans('student.live_in_state_options.UNKNOWN'),
                "OWN" => trans('student.live_in_state_options.OWN'),
                "RENT" => trans('student.live_in_state_options.RENT')
            ],
            'allows_null' => false,
            'tab'   => trans('student.financial_state'),
            // 'default' => 'one',
            // 'allows_multiple' => true, // OPTIONAL; needs you to cast this to array in your model;
        ]);


        // CRUD::addField([
        //     'name' => 'live_in_state',
        //     'label' => trans('student.live_in_state'),
        //     'type' => 'enum',
        //     'wrapper'   => [
        //         'class'      => 'form-group col-md-6'
        //     ],
        //     'tab'   => trans('student.financial_state'),
        // ]);





        CRUD::addField([
            'name' => 'financial_state',
            'label' => trans('student.financial_state'),
            'type' => 'select_from_array',
            'wrapper'   => [
                'class'      => 'form-group col-md-6'
            ],
            'options' => [
                "UNKNOWN" => trans('student.financial_state_options.UNKNOWN'),
                "POOR" => trans('student.financial_state_options.POOR'),
                "AVERAGE" => trans('student.financial_state_options.AVERAGE'),
                "GOOD" => trans('student.financial_state_options.GOOD'),
                "EXCELLENT" => trans('student.financial_state_options.EXCELLENT')
            ],
            'allows_null' => false,
            'tab'   => trans('student.financial_state'),
            // 'default' => 'one',
            // 'allows_multiple' => true, // OPTIONAL; needs you to cast this to array in your model;
        ]);

        // CRUD::addField([
        //     'name' => 'financial_state',
        //     'label' => trans('student.financial_state'),
        //     'type' => 'enum',
        //     'wrapper'   => [
        //         'class'      => 'form-group col-md-6'
        //     ],
        //     'tab'   => trans('student.financial_state'),
        // ]);


        CRUD::addField([
            'name' => 'address',
            'label' => trans('student.address'),
            'tab'   => trans('student.financial_state'),
        ]);
        CRUD::addField([
            'name' => 'financial_details',
            'label' => trans('student.financial_details'),
            'tab'   => trans('student.financial_state'),
        ]);





        CRUD::addField([
            'name' => 'hawza_history',
            'label' => trans('student.hawza_history'),
            'tab'   => trans('student.hawza_history'),
            'wrapper'   => [
                'class'      => 'form-group col-md-6'
            ],
        ]);
        CRUD::addField([
            'name' => 'degree',
            'label' => trans('student.degree'),
            'tab'   => trans('student.hawza_history'),
            'wrapper'   => [
                'class'      => 'form-group col-md-6'
            ],
        ]);
        CRUD::addField([
            'name' => 'hawza_history_details',
            'label' => trans('student.hawza_history_details'),
            'tab'   => trans('student.hawza_history'),
        ]);




        CRUD::addField([
            'name' => 'health_history',
            'label' => trans('student.health_history'),
            'tab'   => trans('student.health_history'),
        ]);
        CRUD::addField([
            'name' => 'health_history_details',
            'label' => trans('student.health_history_details'),
            'tab'   => trans('student.health_history'),
        ]);



        CRUD::addField([
            'name' => 'student_notes',
            'label' => trans('student.student_notes'),
            'tab'   => trans('base.more'),
        ]);


        if ($this->crud->getCurrentEntryId()) {
            $this->crud->addFields([
                [
                    'name'  => 'password',
                    'label' => trans('backpack::permissionmanager.password'),
                    'type'  => 'password',
                    'tab'   => 'password',
                    'attributes' => [
                        'autocomplete' => 'off'
                    ]

                ],
                [
                    'name'  => 'password_confirmation',
                    'label' => trans('backpack::permissionmanager.password_confirmation'),
                    'type'  => 'password',
                    'tab'   => 'password',
                    'attributes' => [
                        'autocomplete' => 'off'
                    ]
                ],
            ]);
        }
        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number']));
         */
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
        $this->addStudentHistoryWidget();
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupShowOperation()
    {
        $this->addStudentHistoryWidget();
    }


    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function addStudentHistoryWidget()
    {
        $this->setupCreateOperation();
        $student = Student::find($this->crud->getCurrentEntryId());

        $classRoomsList = [];
        foreach ($student->classRooms as $key => $classRoom) {

            $data['payments'] = Payment::where([
                ['course_id', $classRoom->course->id],
                ['student_id', $student->id],
            ])->get();

            $data['wrapper_id'] = "classroom_id_{$classRoom->id}";
            $data['classRoom'] = $classRoom;
            $data['course'] = $classRoom->course;
            $data['weekDays'] = [
                7 =>  trans('base.Sunday'),
                1 => trans('base.Monday'),
                2 => trans('base.Tuesday'),
                3 => trans('base.Wednesday'),
                4 => trans('base.Thursday'),
                5 => trans('base.Friday'),
                6 => trans('base.Saturday'),
            ];

            $classRoomsList[] = [
                'type'       => 'card',
                'wrapper' => ['class' => 'col-md-6', 'id' => $data['wrapper_id']], // optional
                'class'   => 'card card-toggle border-success  ', // optional
                'content'    => [
                    'header' => $classRoom->course->long_name, // optional
                    'body'   => view('reports.student.components.widget_classroom_info', $data)
                ]
            ];
        }

        $warpWidgets = [
            'type' => 'div',
            'class' => 'row',
            'content' => $classRoomsList
        ];

        Widget::add($warpWidgets)->to('after_content');
    }


    protected function filters()
    {

        /*
         *      financial_support_status 
         *
         */
        // select2_multiple filter
        $this->crud->addFilter([
            'name'  => 'financial_support_status',
            'type'  => 'select2_multiple',
            'label' => __('student.financial_support_status')
        ], function () {
            return Student::FinancialSupportStatusArray();
        }, function ($values) { // if the filter is active
            $this->crud->addClause('whereIn', 'financial_support_status', json_decode($values));
        });
    }

    /**
     * Store a newly created resource in the database.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store()
    {
        $this->crud->setRequest($this->crud->validateRequest());
        $this->crud->setRequest($this->handlePasswordInput($this->crud->getRequest()));
        $this->crud->unsetValidation(); // validation has already been run

        return $this->traitStore();
    }

    /**
     * Update the specified resource in the database.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update()
    {
        $this->crud->setRequest($this->crud->validateRequest());
        $this->crud->setRequest($this->handlePasswordInput($this->crud->getRequest()));
        $this->crud->unsetValidation(); // validation has already been run

        return $this->traitUpdate();
    }

    /**
     * Handle password input fields.
     */
    protected function handlePasswordInput($request)
    {
        // Remove fields not present on the user.
        $request->request->remove('password_confirmation');
        $request->request->remove('roles_show');
        $request->request->remove('permissions_show');

        // Encrypt password if specified.
        if ($request->input('password')) {
            $request->request->set('password', Hash::make($request->input('password')));
        } else {
            $request->request->remove('password');
        }

        return $request;
    }
}
