<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ClassRoomRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class ClassRoomCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ClassRoomCrudController extends CrudController
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
        CRUD::setModel(\App\Models\ClassRoom::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/classroom');
        CRUD::setEntityNameStrings(trans('classroom.classroom'), trans('classroom.classrooms'));
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
            // any type of relationship
            'name'         => 'course', // name of relationship method in the model
            'type'         => 'relationship',
            'label'        => trans('course.course'), // Table column heading
            // OPTIONAL
            // 'entity'    => 'tags', // the method that defines the relationship in your Model
            'attribute' => 'long_name', // foreign key attribute that is shown to user
            // 'model'     => App\Models\Category::class, // foreign key model
        ],);

        CRUD::addColumn([
            'name' => 'class_room_name',
            'label' => trans('classroom.class_room_name'),
        ]);
        CRUD::addColumn([
            'name' => 'class_room_number',
            'label' => trans('classroom.class_room_number'),
        ]);


        CRUD::addColumn([
            // any type of relationship
            'name'         => 'studentsCount', // name of relationship method in the model
            'type'         => 'relationship_count',
            'label'        => trans('student.students'), // Table column heading
            'suffix' =>  '  ' . trans('student.students_short'), // to show "123 tags" instead of "123 items"

        ],);

        CRUD::addColumn([
            // any type of relationship
            'name'         => 'students', // name of relationship method in the model
            'type'         => 'relationship',
            'label'        => trans('student.students'), // Table column heading
            // OPTIONAL
            // 'entity'    => 'tags', // the method that defines the relationship in your Model
            // 'attribute' => 'name', // foreign key attribute that is shown to user
            // 'model'     => App\Models\Category::class, // foreign key model
        ],);

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

        // select2_ajax filter
        $this->crud->addFilter(
            [
                'name'        => 'course_filter',
                'type'        => 'select2_ajax',
                'label'       => trans('course.course'),
                'placeholder' => trans('course.course')
            ],
            url('api/course'), // the ajax route
            function ($value) { // if the filter is active
                $this->crud->addClause('where', 'course_id', $value);
            }
        );

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
         */

        $this->crud->addButtonFromModelFunction('line', 'print', 'getPrintDropdown');
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(ClassRoomRequest::class);

        CRUD::addField(
            [   // 1-n relationship
                'label'       => trans('course.course'), // Table column heading
                'type'        => "select2_from_ajax",
                'name'        => 'course_id', // the column that contains the ID of that connected entity
                'entity'      => 'course', // the method that defines the relationship in your Model
                'attribute'   => "long_name", // foreign key attribute that is shown to user
                'data_source' => url("api/course"), // url to controller search function (with /{id} should return model)

                // OPTIONAL
                'placeholder'             => "Select a course", // placeholder for the select
                'minimum_input_length'    => 1, // minimum characters to type before querying results
                // 'model'                   => "App\Models\Category", // foreign key model
                // 'dependencies'            => ['category'], // when a dependency changes, this select2 is reset to null
                // 'method'                  => 'GET', // optional - HTTP method to use for the AJAX call (GET, POST)
                // 'include_all_form_fields' => false, // optional - only send the current field through AJAX (for a smaller payload if you're not using multiple chained select2s)
            ],
        );

        CRUD::addField([
            'name' => 'class_room_name',
            'label' => trans('classroom.class_room_name'),
        ]);
        CRUD::addField([
            'name' => 'class_room_number',
            'label' => trans('classroom.class_room_number'),
        ]);
        CRUD::addField([
            // n-n relationship
            'label'       =>  trans('student.students'), // Table column heading
            'type'        => "select2_from_ajax_multiple",
            'name'        => 'students', // a unique identifier (usually the method that defines the relationship in your Model)
            'entity'      => 'students', // the method that defines the relationship in your Model
            'attribute'   => "long_name", // foreign key attribute that is shown to user
            'data_source' => url("api/student"), // url to controller search function (with /{id} should return model)
            'pivot'       => true, // on create&update, do you need to add/delete pivot table entries?

            // OPTIONAL
            'model'                => "App\Models\Student", // foreign key model
            'placeholder'          => "Select a Students", // placeholder for the select
            'minimum_input_length' => 1, // minimum characters to type before querying results
            // 'include_all_form_fields'  => false, // optional - only send the current field through AJAX (for a smaller payload if you're not using multiple chained select2s)

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

        $curricula_list = [];
        $currentClassRoom = \App\Models\ClassRoom::find($this->crud->getCurrentEntryId());
        foreach ($currentClassRoom->course->academicPath->curricula as $curriculum) {
            $curricula_list[$curriculum->id] = $curriculum->curriculumـname;
        }


        CRUD::addField([   // repeatable
            'name'  => 'teachers',
            'label' => trans('classroom.teachers'),
            'type'  => 'repeatable',
            'tab'   => trans('classroom.teachers'),


            // optional
            'new_item_label'  => trans('classroom.teacher'), // customize the text of the button
            'init_rows' => 0, // number of empty rows to be initialized, by default 1
            'min_rows' => 0, // minimum rows allowed, when reached the "delete" buttons will be hidden
            'max_rows' => 50, // maximum rows allowed, when reached the "new item" button will be hidden

            'fields' => [
                [
                    'name'        => 'curriculumـid',
                    'label'       => trans('curriculum.curriculumـname'),
                    'type'        => 'select_from_array',
                    'options'     => $curricula_list,
                    'allows_null' => false,
                    // 'default'     => '',
                    'wrapper' => ['class' => 'form-group col-md-6'],
                    'hint' => trans('studentmark.curriculum_hint'),

                ],
                [
                    'name'    => 'teacher_name',
                    'type'    => 'text',
                    'label'   => trans('classroom.teacher_name'),
                    'wrapper' => ['class' => 'form-group col-md-6'],
                ],
            ],
        ]);


        $week_days = [
            7 =>  trans('base.Sunday'),
            1 => trans('base.Monday'),
            2 => trans('base.Tuesday'),
            3 => trans('base.Wednesday'),
            4 => trans('base.Thursday'),
            5 => trans('base.Friday'),
            6 => trans('base.Saturday'),
        ];

        // Monday, Tuesday, Wednesday, Thursday, Friday, Saturday and Sunday,
        // dd($curricula_list, $day_of_week = date('N', strtotime('Monday')));


        CRUD::addField([   // repeatable
            'name'  => 'attend_table',
            'label' => trans('classroom.attend_table'),
            'type'  => 'repeatable',
            'tab'   => trans('classroom.attend_table'),


            // optional
            'new_item_label'  => trans('classroom.attend_table_single'), // customize the text of the button
            'init_rows' => 0, // number of empty rows to be initialized, by default 1
            'min_rows' => 0, // minimum rows allowed, when reached the "delete" buttons will be hidden
            'max_rows' => 50, // maximum rows allowed, when reached the "new item" button will be hidden

            'fields' => [
                [
                    'name'        => 'curriculumـid',
                    'label'       => trans('curriculum.curriculumـname'),
                    'type'        => 'select_from_array',
                    'options'     => $curricula_list,
                    'allows_null' => false,
                    // 'default'     => '',
                    'wrapper' => ['class' => 'form-group col-md-5'],
                    'hint' => trans('studentmark.curriculum_hint'),

                ],
                [
                    'name'        => 'day',
                    'label'       => trans('base.day'),
                    'type'        => 'select_from_array',
                    'options'     => $week_days,
                    'allows_null' => false,
                    'default'     => 7,
                    'wrapper' => ['class' => 'form-group col-md-3'],

                ],
                [
                    'name'        => 'start_time',
                    'label'       => trans('classroom.start_time'),
                    'type'        => 'time',

                    'wrapper' => ['class' => 'form-group col-md-3'],

                ],
            ],
        ]);
    }
}
