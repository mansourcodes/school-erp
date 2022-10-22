<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ExamToolRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Widget;

/**
 * Class ExamToolCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ExamToolCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\ExamTool::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/examtool');
        CRUD::setEntityNameStrings(trans('examtool.examtool'), trans('examtool.examtools'));
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {

        CRUD::column('id');

        CRUD::column('subject')->label(trans('examtool.subject'));



        CRUD::addColumn([
            'name' => 'course_id',
            'label' => trans('course.course'),
            'type'         => 'relationship',
            // OPTIONAL
            // 'entity'    => 'tags', // the method that defines the relationship in your Model
            'attribute' => 'long_name', // foreign key attribute that is shown to user
            // 'model'     => App\Models\Category::class, // foreign key model
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhereHas('course', function ($query) use ($column, $searchTerm) {
                    $query->where('course_year', 'like', '%' . $searchTerm . '%')
                        ->orWhere('hijri_year', 'like', '%' . $searchTerm . '%')
                        ->orWhere('semester', 'like', '%' . $searchTerm . '%')
                        ->orWhereHas('academicPath', function ($query) use ($column, $searchTerm) {
                            $query->where('academic_path_name', 'like', '%' . $searchTerm . '%');
                        });
                });
            }
        ]);

        // CRUD::column('file')->label(trans('examtool.file'));
        CRUD::column('zip_file_size')->label(trans('examtool.zip_file_size'))->prefix(' MB ');
        CRUD::column('status')->label(trans('examtool.status'));
        // CRUD::column('meta')->label(trans('examtool.meta'));

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']); 
         */

        $this->notesWidgets();
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(ExamToolRequest::class);

        CRUD::field('subject');


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
                'wrapper'   => [
                    'class'      => 'form-group col-6'
                ],
            ],
        );



        // CRUD::field('file');
        CRUD::addField(
            [   // Upload
                'name'      => 'file',
                'label'     => trans('examtool.file'),
                'type'      => 'upload',
                'upload'    => true,
                'wrapper'   => [
                    'class'      => 'form-group col-6 text-left'
                ],
            ],
        );

        // CRUD::field('zip_file_size');
        CRUD::field('status')->default('On Process')->type('hidden');

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number'])); 
         */

        $this->notesWidgets();
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




    protected function notesWidgets()
    {


        $widget_definition_array = [
            'type'       => 'card',
            'wrapper' => ['class' => 'col-md-12'], // optional
            'class'   => 'card border-danger bg-info ', // optional
            'content'    => [
                'header' => trans('base.notice'), // optional
                'body'   => trans('examtool.upload_note'),
            ]
        ];

        Widget::add($widget_definition_array)->to('before_content');
    }
}
