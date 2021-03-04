<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests\StudentMarksRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Models\StudentMarks;
use App\Models\Course;
use App\Models\Curriculum;

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
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;


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

        // CRUD::addColumn([
        //     'name' => 'created_at',
        //     'label' => trans('base.created_at'),
        //     'type' => 'date'
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
        $this->crud->setActionsColumnPriority(0);

        // $this->crud->addButtonFromModelFunction('line', 'print', StudentMarks::getPrintDropdown(1));
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
        $currentStudentMarks = \App\Models\StudentMarks::find($this->crud->getCurrentEntryId());
        foreach ($currentStudentMarks->course->academicPath->curricula as $curriculum) {
            $curricula_list[$curriculum->id] = $curriculum->curriculumـname;
        }

        CRUD::addField([   // repeatable
            'name'  => 'marks',
            'label' => trans('studentmark.marks'),
            'type'  => 'repeatable',

            // optional
            'new_item_label'  => trans('curriculum.add_a_curriculum'), // customize the text of the button
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
                    'wrapper' => ['class' => 'form-group col-md-4'],
                    'hint' => trans('studentmark.curriculum_hint'),

                ],
                [
                    'name'    => 'total_mark',
                    'type'    => 'number',
                    'label'   => trans('studentmark.total_mark'),
                    'wrapper' => ['class' => 'form-group col-md-4'],
                    'hint' => trans('studentmark.total_mark_hint'),

                ],
                [
                    'name'    => 'final_grade',
                    'type'    => 'text',
                    'label'   => trans('studentmark.final_grade'),
                    'wrapper' => ['class' => 'form-group col-md-4'],
                    'hint' => trans('studentmark.final_grade_hint'),
                ],


                [   // Table
                    'name'            => 'marks_details',
                    'label'           => trans('studentmark.marks_details'),
                    'type'            => 'table',
                    'entity_singular' => '', // used on the "Add X" button
                    'columns'         => [
                        'label'  => trans('studentmark.marks_details_label'),
                        'mark'  => trans('studentmark.marks_details_mark'),
                    ],
                    'max' => 20, // maximum rows allowed in the table
                    'min' => 0, // minimum rows allowed in the table
                ],
            ],

        ]);
    }



    use \Backpack\CRUD\app\Http\Controllers\Operations\FetchOperation;

    public function fetchCurriculum()
    {
        return $this->fetch(\App\Models\Curriculum::class);
    }
}
