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

        CRUD::addColumn([
            'name' => 'class_room_name',
            'label' => trans('classroom.class_room_name'),
        ]);
        CRUD::addColumn([
            'name' => 'class_room_number',
            'label' => trans('classroom.class_room_number'),
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
        CRUD::setValidation(ClassRoomRequest::class);


        CRUD::addField([
            'name' => 'class_room_name',
            'label' => trans('classroom.class_room_name'),
        ]);
        CRUD::addField([
            'name' => 'class_room_number',
            'label' => trans('classroom.class_room_number'),
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
