<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\StudentRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

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
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

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
            'wrapper'   => [
                'class'      => 'col-6'
            ],
        ]);
        CRUD::addField([
            'name' => 'email',
            'label' => trans('student.email'),
            'wrapper'   => [
                'class'      => 'col-6'
            ],
        ]);


        CRUD::addField([
            'name' => 'mobile',
            'label' => trans('student.mobile'),
            'wrapper'   => [
                'class'      => 'col-6'
            ],
        ]);
        CRUD::addField([
            'name' => 'mobile2',
            'label' => trans('student.mobile2'),
            'wrapper'   => [
                'class'      => 'col-6'
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
                'class'      => 'col-6'
            ],
        ]);

        CRUD::addField([
            'name' => 'registration_at',
            'label' => trans('student.registration_at'),
            'type' => 'date_picker',
            'date_picker_options' => [
                'todayBtn' => 'linked',
                // 'format'   => 'dd-mm-yyyy',
                'language' => 'ar',
            ],
            'wrapper'   => [
                'class'      => 'col-6'
            ],
        ]);


        CRUD::addField([
            'name' => 'relationshipـstate',
            'label' => trans('student.relationshipـstate'),
            'type' => 'select_from_array',
            'wrapper'   => [
                'class'      => 'col-6'
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
        //         'class'      => 'col-6'
        //     ],
        //     'tab'   => trans('student.relationshipـstate'),
        // ]);
        CRUD::addField([
            'name' => 'family_depends',
            'label' => trans('student.family_depends'),
            'wrapper'   => [
                'class'      => 'col-6'
            ],
            'tab'   => trans('student.relationshipـstate'),
        ]);
        CRUD::addField([
            'name' => 'family_members',
            'label' => trans('student.family_members'),
            'wrapper'   => [
                'class'      => 'col-6'
            ],
            'tab'   => trans('student.relationshipـstate'),
        ]);


        CRUD::addField([
            'name' => 'live_inـstate',
            'label' => trans('student.live_inـstate'),
            'type' => 'select_from_array',
            'wrapper'   => [
                'class'      => 'col-6'
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
        //         'class'      => 'col-6'
        //     ],
        //     'tab'   => trans('student.financialـstate'),
        // ]);



        CRUD::addField([
            'name' => 'financialـstate',
            'label' => trans('student.financialـstate'),
            'type' => 'select_from_array',
            'wrapper'   => [
                'class'      => 'col-6'
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
        //         'class'      => 'col-6'
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
                'class'      => 'col-6'
            ],
        ]);
        CRUD::addField([
            'name' => 'degree',
            'label' => trans('student.degree'),
            'tab'   => trans('student.hawzaـhistory'),
            'wrapper'   => [
                'class'      => 'col-6'
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
    }
}
