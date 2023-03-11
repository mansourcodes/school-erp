<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\StudentRequest;
use App\Models\Account\Payment;
use App\Models\Student;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Widget;
use Illuminate\Support\Carbon;

/**
 * Class StudentCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class StudentCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
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
            'name' => 'student_name',
            'label' => trans('student.student_name'),
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
            'name' => 'student_name',
            'label' => trans('student.student_name'),
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
            'name' => 'parentsـstate',
            'label' => trans('student.parentsـstate'),
            'type' => 'select_from_array',
            'wrapper'   => [
                'class'      => 'form-group col-md-6'
            ],
            'options' => [
                "BOTH" => trans('student.parentsـstate_options.BOTH'),
                "MOTHER" => trans('student.parentsـstate_options.MOTHER'),
                "FATHER" => trans('student.parentsـstate_options.FATHER'),
                "NONE" => trans('student.parentsـstate_options.NONE')
            ],
            'allows_null' => false,
            'tab'   => trans('student.relationshipـstate'),
            // 'default' => 'one',
            // 'allows_multiple' => true, // OPTIONAL; needs you to cast this to array in your model;
        ]);
        CRUD::addField([
            'name' => 'relationshipـstate',
            'label' => trans('student.relationshipـstate'),
            'type' => 'select_from_array',
            'wrapper'   => [
                'class'      => 'form-group col-md-6'
            ],
            'options' => [
                "UNKNOWN" => trans('student.relationshipـstate_options.UNKNOWN'),
                "SINGLE" => trans('student.relationshipـstate_options.SINGLE'),
                "MARRIED" => trans('student.relationshipـstate_options.MARRIED')
            ],
            'allows_null' => false,
            'tab'   => trans('student.relationshipـstate'),
            // 'default' => 'one',
            // 'allows_multiple' => true, // OPTIONAL; needs you to cast this to array in your model;
        ]);
        // CRUD::addField([
        //     'name' => 'relationshipـstate',
        //     'label' => trans('student.relationshipـstate'),
        //     'type' => 'enum',
        //     'wrapper'   => [
        //         'class'      => 'form-group col-md-6'
        //     ],
        //     'tab'   => trans('student.relationshipـstate'),
        // ]);
        CRUD::addField([
            'name' => 'family_depends',
            'label' => trans('student.family_depends'),
            'wrapper'   => [
                'class'      => 'form-group col-md-6'
            ],
            'tab'   => trans('student.relationshipـstate'),
        ]);
        CRUD::addField([
            'name' => 'family_members',
            'label' => trans('student.family_members'),
            'wrapper'   => [
                'class'      => 'form-group col-md-6'
            ],
            'tab'   => trans('student.relationshipـstate'),
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
            'tab'   => trans('student.financialـstate'),
            // 'default' => 'one',
            // 'allows_multiple' => true, // OPTIONAL; needs you to cast this to array in your model;
        ]);

        CRUD::addField([
            'name' => 'live_inـstate',
            'label' => trans('student.live_inـstate'),
            'type' => 'select_from_array',
            'wrapper'   => [
                'class'      => 'form-group col-md-6'
            ],
            'options' => [
                "UNKNOWN" => trans('student.live_inـstate_options.UNKNOWN'),
                "OWN" => trans('student.live_inـstate_options.OWN'),
                "RENT" => trans('student.live_inـstate_options.RENT')
            ],
            'allows_null' => false,
            'tab'   => trans('student.financialـstate'),
            // 'default' => 'one',
            // 'allows_multiple' => true, // OPTIONAL; needs you to cast this to array in your model;
        ]);


        // CRUD::addField([
        //     'name' => 'live_inـstate',
        //     'label' => trans('student.live_inـstate'),
        //     'type' => 'enum',
        //     'wrapper'   => [
        //         'class'      => 'form-group col-md-6'
        //     ],
        //     'tab'   => trans('student.financialـstate'),
        // ]);





        CRUD::addField([
            'name' => 'financialـstate',
            'label' => trans('student.financialـstate'),
            'type' => 'select_from_array',
            'wrapper'   => [
                'class'      => 'form-group col-md-6'
            ],
            'options' => [
                "UNKNOWN" => trans('student.financialـstate_options.UNKNOWN'),
                "POOR" => trans('student.financialـstate_options.POOR'),
                "AVERAGE" => trans('student.financialـstate_options.AVERAGE'),
                "GOOD" => trans('student.financialـstate_options.GOOD'),
                "EXCELLENT" => trans('student.financialـstate_options.EXCELLENT')
            ],
            'allows_null' => false,
            'tab'   => trans('student.financialـstate'),
            // 'default' => 'one',
            // 'allows_multiple' => true, // OPTIONAL; needs you to cast this to array in your model;
        ]);

        // CRUD::addField([
        //     'name' => 'financialـstate',
        //     'label' => trans('student.financialـstate'),
        //     'type' => 'enum',
        //     'wrapper'   => [
        //         'class'      => 'form-group col-md-6'
        //     ],
        //     'tab'   => trans('student.financialـstate'),
        // ]);


        CRUD::addField([
            'name' => 'address',
            'label' => trans('student.address'),
            'tab'   => trans('student.financialـstate'),
        ]);
        CRUD::addField([
            'name' => 'financial_details',
            'label' => trans('student.financial_details'),
            'tab'   => trans('student.financialـstate'),
        ]);





        CRUD::addField([
            'name' => 'hawzaـhistory',
            'label' => trans('student.hawzaـhistory'),
            'tab'   => trans('student.hawzaـhistory'),
            'wrapper'   => [
                'class'      => 'form-group col-md-6'
            ],
        ]);
        CRUD::addField([
            'name' => 'degree',
            'label' => trans('student.degree'),
            'tab'   => trans('student.hawzaـhistory'),
            'wrapper'   => [
                'class'      => 'form-group col-md-6'
            ],
        ]);
        CRUD::addField([
            'name' => 'hawzaـhistory_details',
            'label' => trans('student.hawzaـhistory_details'),
            'tab'   => trans('student.hawzaـhistory'),
        ]);




        CRUD::addField([
            'name' => 'healthـhistory',
            'label' => trans('student.healthـhistory'),
            'tab'   => trans('student.healthـhistory'),
        ]);
        CRUD::addField([
            'name' => 'healthـhistory_details',
            'label' => trans('student.healthـhistory_details'),
            'tab'   => trans('student.healthـhistory'),
        ]);



        CRUD::addField([
            'name' => 'student_notes',
            'label' => trans('student.student_notes'),
            'tab'   => trans('base.more'),
        ]);



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
}
