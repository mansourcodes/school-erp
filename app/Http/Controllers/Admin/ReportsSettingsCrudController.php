<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ReportsSettingsRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class ReportsSettingsCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ReportsSettingsCrudController extends CrudController
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
        CRUD::setModel(\App\Models\ReportsSettings::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/reportssettings');
        CRUD::setEntityNameStrings(trans('reportssettings.reportssettings'),  trans('reportssettings.reportssettings'));
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        // CRUD::column('key');
        // // CRUD::column('type');
        // CRUD::column('value');



        CRUD::addColumn([
            'name' => 'description',
            'label' => trans('reportssettings.description'),
        ]);

        CRUD::addColumn([
            'name' => 'value',
            'label' => trans('reportssettings.value'),
        ]);
        CRUD::addColumn([
            'name' => 'key',
            'label' => trans('reportssettings.key'),
        ]);

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
        CRUD::setValidation(ReportsSettingsRequest::class);


        CRUD::addField([
            'name' => 'key',
            'label' => trans('reportssettings.key'),
        ]);

        CRUD::addField([
            'name' => 'description',
            'label' => trans('reportssettings.description'),
        ]);


        CRUD::addField([
            'name'        => 'type',
            'label'       => trans('reportssettings.type'),
            'type'        => 'select_from_array',
            'options'     => [
                'string' => 'نص',
                'number' => 'رقم',
                'checkbox' => 'تفعيل او تعطيل',
                'ckeditor' => 'نص طويل',
                'markstable' => 'تفاصيل الدرجات',
            ],
            'allows_null' => false,
            'default'     => 'string',
            'wrapper' => ['class' => 'form-group col-md-4'],
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

        CRUD::setValidation(ReportsSettingsRequest::class);


        $currentReportsSettings = \App\Models\ReportsSettings::find($this->crud->getCurrentEntryId());




        CRUD::addField([
            'name' => 'description',
            'type' => 'custom_html',
            'value' => "<b> {$currentReportsSettings->description} </b>"
        ]);

        // CRUD::addField([
        //     'name' => 'description',
        //     'label' => trans('reportssettings.description'),
        // ]);

        switch ($currentReportsSettings->type) {
            case 'checkbox':
                CRUD::addField([
                    'name'        => 'value',
                    'label'       => '',
                    'type'        => 'checkbox',
                ]);
                break;

            case 'ckeditor':
                CRUD::addField([
                    'name'        => 'value',
                    'label'       => '',
                    'type'        => 'ckeditor',
                    // optional:
                    // 'extra_plugins' => ['oembed', 'widget'],
                    'options'       => [
                        'autoGrow_minHeight'   => 400,
                        'autoGrow_bottomSpace' => 150,
                        // 'removePlugins'        => 'resize,maximize',
                    ]
                ]);
                break;

            default:
            case 'string':
                CRUD::addField([
                    'name'        => 'value',
                    'label'       => '',
                    'type'        => 'textarea',
                ]);
                break;
        }




        CRUD::addField([
            'name' => 'key',
            'type' => 'hidden',
        ]);

        CRUD::addField([
            'name' => 'custom_html',
            'type' => 'custom_html',
            'value' => trans('reportssettings.key') . ": {$currentReportsSettings->key} "
        ]);
    }
}
