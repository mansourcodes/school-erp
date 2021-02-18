<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\AcademicPathRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class AcademicPathCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class AcademicPathCrudController extends CrudController
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
        CRUD::setModel(\App\Models\AcademicPath::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/academicpath');
        CRUD::setEntityNameStrings(trans('academicpath.academicpath'),  trans('academicpath.academicpaths'));
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
            'name' => 'academic_path_name',
            'label' => trans('academicpath.academic_path_name'),
        ]);
        CRUD::addColumn([
            'name' => 'academic_path_type',
            'label' => trans('academicpath.academic_path_type'),
        ]);



        CRUD::addColumn([
            // any type of relationship
            'name'         => 'curricula', // name of relationship method in the model
            'type'         => 'relationship',
            'label'        => trans('curriculum.curricula'), // Table column heading
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
        CRUD::setValidation(AcademicPathRequest::class);



        CRUD::addField([
            'name' => 'academic_path_name',
            'label' => trans('academicpath.academic_path_name'),
        ]);
        CRUD::addField([
            'name' => 'academic_path_type',
            'label' => trans('academicpath.academic_path_type'),
        ]);
        CRUD::addField(
            [    // Select2Multiple = n-n relationship (with pivot table)
                'label'     => trans('curriculum.curricula'),
                'type'      => 'select2_multiple',
                'name'      => 'curricula', // the method that defines the relationship in your Model

                // optional
                'entity'    => 'curricula', // the method that defines the relationship in your Model
                'model'     => "App\Models\Curriculum", // foreign key model
                'attribute' => 'long_name', // foreign key attribute that is shown to user
                'pivot'     => true, // on create&update, do you need to add/delete pivot table entries?
                'select_all' => true, // show Select All and Clear buttons?

                // optional
                // 'options'   => (function ($query) {
                //     return $query->orderBy('name', 'ASC')->where('depth', 1)->get();
                // }), // force the related options to be a custom query, instead of all(); you can use this to filter the results show in the select
            ],
        );

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
