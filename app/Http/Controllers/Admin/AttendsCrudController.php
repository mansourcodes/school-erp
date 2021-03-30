<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AttendsRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class AttendsCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class AttendsCrudController extends CrudController
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
        CRUD::setModel(\App\Models\Attends::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/attends');
        CRUD::setEntityNameStrings(trans('attend.attend'), trans('attend.attends'));
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::column('date');
        CRUD::column('start_time');
        CRUD::column('class_room_id');
        CRUD::column('course_id');

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
         */
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(AttendsRequest::class);


        CRUD::addField(
            [
                'name'        => 'date',
                'label'       => trans('base.date'),
                'type'        => 'date',
                'wrapper' => ['class' => 'form-group col-md-6'],
            ]
        );

        CRUD::addField(
            [
                'name'        => 'start_time',
                'label'       => trans('classroom.start_time'),
                'type'        => 'time',
                'wrapper' => ['class' => 'form-group col-md-6'],
            ]
        );

        CRUD::addField(
            [   // 1-n relationship
                'label'       => trans('classroom.classroom'), // Table column heading
                'type'        => "select2_from_ajax",
                'name'        => 'class_room_id', // the column that contains the ID of that connected entity
                'entity'      => 'classRoom', // the method that defines the relationship in your Model
                'attribute'   => "long_name", // foreign key attribute that is shown to user
                'data_source' => url("api/classroom"), // url to controller search function (with /{id} should return model)

                // OPTIONAL
                'placeholder'             => "Select a classroom", // placeholder for the select
                'minimum_input_length'    => 1, // minimum characters to type before querying results
                // 'model'                   => "App\Models\Category", // foreign key model
                // 'dependencies'            => ['category'], // when a dependency changes, this select2 is reset to null
                // 'method'                  => 'GET', // optional - HTTP method to use for the AJAX call (GET, POST)
                // 'include_all_form_fields' => false, // optional - only send the current field through AJAX (for a smaller payload if you're not using multiple chained select2s)

                'wrapper' => ['class' => 'form-group col-md-6'],
            ],
        );


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

                'wrapper' => ['class' => 'form-group col-md-6'],
            ],
        );



        /*/----------------------------------------
        ********---------------------        attend
        //*/ //------------------------------------

        CRUD::addField([
            // n-n relationship
            'label'       =>  trans('attend.attend_students'), // Table column heading
            'type'        => "select2_from_ajax_multiple",
            'name'        => 'attendStudents', // a unique identifier (usually the method that defines the relationship in your Model)
            'entity'      => 'attendStudents', // the method that defines the relationship in your Model
            'attribute'   => "long_name", // foreign key attribute that is shown to user
            'data_source' => url("api/student"), // url to controller search function (with /{id} should return model)
            'pivot'       => true, // on create&update, do you need to add/delete pivot table entries?

            // OPTIONAL
            'model'                => "App\Models\Student", // foreign key model
            'placeholder'          => "Select a Students", // placeholder for the select
            'minimum_input_length' => 1, // minimum characters to type before querying results
            // 'include_all_form_fields'  => false, // optional - only send the current field through AJAX (for a smaller payload if you're not using multiple chained select2s)

        ]);


        /*/----------------------------------------
        ********---------------------        absent
        //*/ //------------------------------------

        CRUD::addField([
            // n-n relationship
            'label'       =>  trans('attend.absent_students'), // Table column heading
            'type'        => "select2_from_ajax_multiple",
            'name'        => 'absentStudents', // a unique identifier (usually the method that defines the relationship in your Model)
            'entity'      => 'absentStudents', // the method that defines the relationship in your Model
            'attribute'   => "long_name", // foreign key attribute that is shown to user
            'data_source' => url("api/student"), // url to controller search function (with /{id} should return model)
            'pivot'       => true, // on create&update, do you need to add/delete pivot table entries?

            // OPTIONAL
            'model'                => "App\Models\Student", // foreign key model
            'placeholder'          => "Select a Students", // placeholder for the select
            'minimum_input_length' => 1, // minimum characters to type before querying results
            // 'include_all_form_fields'  => false, // optional - only send the current field through AJAX (for a smaller payload if you're not using multiple chained select2s)
            'tab'   => trans('attend.absent_students'),

        ]);

        /*/----------------------------------------
        ********---------------------        absent w E
        //*/ //------------------------------------

        CRUD::addField([
            // n-n relationship
            'label'       =>  trans('attend.absent_w_excuse_students'), // Table column heading
            'type'        => "select2_from_ajax_multiple",
            'name'        => 'absentWithExcuseStudents', // a unique identifier (usually the method that defines the relationship in your Model)
            'entity'      => 'absentWithExcuseStudents', // the method that defines the relationship in your Model
            'attribute'   => "long_name", // foreign key attribute that is shown to user
            'data_source' => url("api/student"), // url to controller search function (with /{id} should return model)
            'pivot'       => true, // on create&update, do you need to add/delete pivot table entries?

            // OPTIONAL
            'model'                => "App\Models\Student", // foreign key model
            'placeholder'          => "Select a Students", // placeholder for the select
            'minimum_input_length' => 1, // minimum characters to type before querying results
            // 'include_all_form_fields'  => false, // optional - only send the current field through AJAX (for a smaller payload if you're not using multiple chained select2s)
            'tab'   => trans('attend.absent_students'),

        ]);


        /*/----------------------------------------
        ********---------------------        late
        //*/ //------------------------------------

        CRUD::addField([
            // n-n relationship
            'label'       =>  trans('attend.late_students'), // Table column heading
            'type'        => "select2_from_ajax_multiple",
            'name'        => 'lateStudents', // a unique identifier (usually the method that defines the relationship in your Model)
            'entity'      => 'lateStudents', // the method that defines the relationship in your Model
            'attribute'   => "long_name", // foreign key attribute that is shown to user
            'data_source' => url("api/student"), // url to controller search function (with /{id} should return model)
            'pivot'       => true, // on create&update, do you need to add/delete pivot table entries?

            // OPTIONAL
            'model'                => "App\Models\Student", // foreign key model
            'placeholder'          => "Select a Students", // placeholder for the select
            'minimum_input_length' => 1, // minimum characters to type before querying results
            // 'include_all_form_fields'  => false, // optional - only send the current field through AJAX (for a smaller payload if you're not using multiple chained select2s)
            'tab'   => trans('attend.late_students'),

        ]);

        /*/----------------------------------------
        ********---------------------        late w E
        //*/ //------------------------------------

        CRUD::addField([
            // n-n relationship
            'label'       =>  trans('attend.late_w_excuse_students'), // Table column heading
            'type'        => "select2_from_ajax_multiple",
            'name'        => 'lateWithExcuseStudents', // a unique identifier (usually the method that defines the relationship in your Model)
            'entity'      => 'lateWithExcuseStudents', // the method that defines the relationship in your Model
            'attribute'   => "long_name", // foreign key attribute that is shown to user
            'data_source' => url("api/student"), // url to controller search function (with /{id} should return model)
            'pivot'       => true, // on create&update, do you need to add/delete pivot table entries?

            // OPTIONAL
            'model'                => "App\Models\Student", // foreign key model
            'placeholder'          => "Select a Students", // placeholder for the select
            'minimum_input_length' => 1, // minimum characters to type before querying results
            // 'include_all_form_fields'  => false, // optional - only send the current field through AJAX (for a smaller payload if you're not using multiple chained select2s)
            'tab'   => trans('attend.late_students'),

        ]);
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
    }
}
