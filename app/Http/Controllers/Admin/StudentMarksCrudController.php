<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\StudentMarksRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class StudentMarksCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class StudentMarksCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation {
        search as traitSearch;
    }
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
        CRUD::setModel(\App\Models\StudentMarks::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/studentmarks');
        CRUD::setEntityNameStrings(trans('studentmark.studentmark'), trans('studentmark.studentsmarks'));
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
            'name' => 'student',
            'label' => trans('student.student_name'),
            'type'         => 'relationship',
            // OPTIONAL
            // 'entity'    => 'tags', // the method that defines the relationship in your Model
            'attribute' => 'long_name', // foreign key attribute that is shown to user
            // 'model'     => App\Models\Category::class, // foreign key model
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhereHas('student', function ($query) use ($column, $searchTerm) {
                    $query->where('student_name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('mobile', 'like', '%' . $searchTerm . '%')
                        ->orWhere('mobile2', 'like', '%' . $searchTerm . '%')
                        ->orWhere('cpr', 'like', '%' . $searchTerm . '%');
                });
            }
        ]);


        CRUD::addColumn([
            'name' => 'course',
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

        // CRUD::addColumn([
        //     'name' => 'marks',
        //     'label' => trans('studentmark.marks'),
        // ]);

        CRUD::addColumn([
            'name' => 'created_at',
            'label' => trans('base.created_at'),
            'type' => 'date'
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
        CRUD::setValidation(StudentMarksRequest::class);



        CRUD::addField(
            [   // 1-n relationship
                'label'       => trans('student.student'), // Table column heading
                'type'        => "select2_from_ajax",
                'name'        => 'student_id', // the column that contains the ID of that connected entity
                'entity'      => 'student', // the method that defines the relationship in your Model
                'attribute'   => "long_name", // foreign key attribute that is shown to user
                'data_source' => url("api/student"), // url to controller search function (with /{id} should return model)

                // OPTIONAL
                'placeholder'             => "Select a student", // placeholder for the select
                'minimum_input_length'    => 1, // minimum characters to type before querying results
                // 'model'                   => "App\Models\Category", // foreign key model
                // 'dependencies'            => ['category'], // when a dependency changes, this select2 is reset to null
                // 'method'                  => 'GET', // optional - HTTP method to use for the AJAX call (GET, POST)
                // 'include_all_form_fields' => false, // optional - only send the current field through AJAX (for a smaller payload if you're not using multiple chained select2s)
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
            ],
        );




        CRUD::addField([
            'name' => 'marks',
            'label' => trans('studentmark.marks'),

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


    // /**
    //  * The search function that is called by the data table.
    //  *
    //  * @return array JSON Array of cells in HTML form.
    //  */
    // public function search()
    // {

    //     $this->crud->hasAccessOrFail('list');

    //     // add the primary key, even though you don't show it,
    //     // otherwise the buttons won't work
    //     $columns = collect($this->crud->columns)->pluck('name')->merge($this->crud->model->getKeyName())->toArray();

    //     // HERE'S WHAT I'VE JUST ADDED
    //     $this->crud->addClause('with', 'user');
    //     $this->crud->addClause('with', 'product');
    //     $this->crud->orderBy('username');
    //     $columns = $columns->merge(['username', 'products.name']);
    //     // END OF WHAT I'VE JUST ADDED

    //     // structure the response in a DataTable-friendly way
    //     $dataTable = new DataTable($this->crud->query, $columns);

    //     // make the datatable use the column types instead of just echoing the text
    //     $dataTable->setFormatRowFunction(function ($entry) {
    //         // get the actual HTML for each row's cell
    //         $row_items = $this->crud->getRowViews($entry, $this->crud);

    //         // add the buttons as the last column
    //         if ($this->crud->buttons->where('stack', 'line')->count()) {
    //             $row_items[] = \View::make('crud::inc.button_stack', ['stack' => 'line'])
    //                 ->with('crud', $this->crud)
    //                 ->with('entry', $entry)
    //                 ->render();
    //         }

    //         // add the details_row buttons as the first column
    //         if ($this->crud->details_row) {
    //             array_unshift($row_items, \View::make('crud::columns.details_row_button')
    //                 ->with('crud', $this->crud)
    //                 ->with('entry', $entry)
    //                 ->render());
    //         }

    //         return $row_items;
    //     });

    //     return $dataTable->make();
    // }
}
