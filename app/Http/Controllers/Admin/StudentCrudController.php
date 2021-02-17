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
            'name' => 'email',
            'label' => trans('student.email'),
        ]);


        CRUD::addColumn([
            'name' => 'created_at',
            'label' => trans('base.created_at'),
        ]);

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
        ]);
        CRUD::addField([
            'name' => 'email',
            'label' => trans('student.email'),
        ]);





        CRUD::addField([
            'name' => 'mobile',
            'label' => trans('student.mobile'),
        ]);
        CRUD::addField([
            'name' => 'mobile2',
            'label' => trans('student.mobile2'),
        ]);





        CRUD::addField([
            'name' => 'dob',
            'label' => trans('student.dob'),
        ]);
        CRUD::addField([
            'name' => 'address',
            'label' => trans('student.address'),
        ]);





        CRUD::addField([
            'name' => 'relationshipـstate',
            'label' => trans('student.relationshipـstate'),
            'type' => 'enum'
        ]);
        CRUD::addField([
            'name' => 'family_depends',
            'label' => trans('student.family_depends'),
        ]);
        CRUD::addField([
            'name' => 'family_members',
            'label' => trans('student.family_members'),
        ]);





        CRUD::addField([
            'name' => 'live_inـstate',
            'label' => trans('student.live_inـstate'),
            'type' => 'enum'
        ]);

        CRUD::addField([
            'name' => 'financialـstate',
            'label' => trans('student.financialـstate'),
            'type' => 'enum'
        ]);
        CRUD::addField([
            'name' => 'financial_details',
            'label' => trans('student.financial_details'),
        ]);





        CRUD::addField([
            'name' => 'degree',
            'label' => trans('student.degree'),
        ]);
        CRUD::addField([
            'name' => 'hawzaـhistory',
            'label' => trans('student.hawzaـhistory'),
        ]);
        CRUD::addField([
            'name' => 'hawzaـhistory_details',
            'label' => trans('student.hawzaـhistory_details'),
        ]);




        CRUD::addField([
            'name' => 'healthـhistory',
            'label' => trans('student.healthـhistory'),
        ]);
        CRUD::addField([
            'name' => 'healthـhistory_details',
            'label' => trans('student.healthـhistory_details'),
        ]);


        CRUD::addField([
            'name' => 'registration_at',
            'label' => trans('student.registration_at'),
        ]);
        CRUD::addField([
            'name' => 'student_notes',
            'label' => trans('student.student_notes'),
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
