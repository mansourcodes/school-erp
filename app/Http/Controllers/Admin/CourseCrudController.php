<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CourseRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\DB;

/**
 * Class CourseCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CourseCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\FetchOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CloneOperation {
        clone as traitClone;
    }

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Course::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/course');
        CRUD::setEntityNameStrings(trans('course.course'), trans('course.courses'));
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
            'name'  => 'id',
        ]);


        CRUD::addColumn([
            'name'  => 'is_active',
            'label' => trans('course.active_state'),
            'type'  => 'boolean',
            // optionally override the Yes/No texts
            'options' => [
                0 => '<i class="las btn btn-light la-eye-slash"></i>',
                1 => '<i class="las btn btn-success la-eye"></i>'
            ]
        ],);

        CRUD::addColumn([
            // any type of relationship
            'name'         => 'AcademicPath', // name of relationship method in the model
            'type'         => 'relationship',
            'label'        => trans('academicpath.academicpath'), // Table column heading
            // OPTIONAL
            // 'entity'    => 'tags', // the method that defines the relationship in your Model
            // 'attribute' => 'name', // foreign key attribute that is shown to user
            // 'model'     => App\Models\Category::class, // foreign key model
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhereHas('academicPath', function ($query) use ($column, $searchTerm) {
                    $query->where('academic_path_name', 'like', '%' . $searchTerm . '%');
                });
            }
        ],);



        CRUD::addColumn([
            'name' => 'course_year',
            'label' => trans('course.course_year'),
        ]);
        // CRUD::addColumn([
        //     'name' => 'hijri_year',
        //     'label' => trans('course.hijri_year'),
        // ]);
        CRUD::addColumn([
            'name' => 'semester',
            'label' => trans('course.semester'),
        ]);

        // CRUD::addColumn([
        //     'name' => 'start_date',
        //     'label' => trans('course.start_date'),
        //     'type'        => 'date',

        // ]);
        // CRUD::addColumn([
        //     'name' => 'end_date',
        //     'label' => trans('course.end_date'),
        //     'type'        => 'date',

        // ]);

        // CRUD::addColumn([
        //     'name' => 'duration',
        //     'label' => trans('course.duration'),
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

        $this->crud->addFilter(
            [
                'type'  => 'text',
                'name'  => 'course_year_filter',
                'label' => trans('course.course_year')
            ],
            false,
            function ($value) { // if the filter is active
                $this->crud->addClause('where', 'course_year', 'LIKE', "%$value%");
            }
        );

        $this->crud->addFilter(
            [
                'type'  => 'text',
                'name'  => 'hijri_year_filter',
                'label' => trans('course.hijri_year')
            ],
            false,
            function ($value) { // if the filter is active
                $this->crud->addClause('where', 'hijri_year', 'LIKE', "%$value%");
            }
        );

        $this->crud->addFilter(
            [
                'type'  => 'text',
                'name'  => 'semester_filter',
                'label' => trans('course.semester')
            ],
            false,
            function ($value) { // if the filter is active
                $this->crud->addClause('where', 'semester', 'LIKE', "%$value%");
            }
        );

        $this->crud->addFilter([
            'name'  => 'is_active',
            'type'  => 'select2',
            'label' => trans('course.active_state'),
        ], function () {
            return [
                1 => trans('course.active'),
                2 => trans('course.not_active'),
            ];
        }, function ($value) { // if the filter is active

            $this->crud->addClause('where', 'is_active', ($value == 1));
        });


        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
         */
        $this->crud->addButtonFromModelFunction('line', 'get_print_dropdown', 'getPrintDropdown', 'beginning');
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(CourseRequest::class);

        CRUD::addField([
            'name' => 'is_active',
            'label' => trans('course.is_active'),
            'type'        => 'checkbox',
            'default'        => true,

        ]);

        CRUD::addField(
            [
                // relationship
                'type' => "relationship",
                'name' => 'academic_path_id', // the method on your model that defines the relationship
                'ajax' => true,

                // OPTIONALS:
                'label' => trans('academicpath.academicpath'),
                'attribute' => "academic_path_name", // foreign key attribute that is shown to user (identifiable attribute)
                'entity' => 'AcademicPath', // the method that defines the relationship in your Model
                // 'model' => "App\Models\Category", // foreign key Eloquent model
                'placeholder' => "Select a Academic Path", // placeholder for the select2 input

                // AJAX OPTIONALS:
                'data_source' => backpack_url("course/fetch/academicpath"), // url to controller search function (with /{id} should return model)
                // 'minimum_input_length' => 2, // minimum characters to type before querying results
                // 'dependencies'         => ['academicpath'], // when a dependency changes, this select2 is reset to null
                // 'include_all_form_fields'  => true, // optional - only send the current field through AJAX (for a smaller payload if you're not using multiple chained select2s)
            ]
        );


        CRUD::addField([
            'name' => 'course_year',
            'label' => trans('course.course_year'),
            'wrapper' => ['class' => 'form-group col-md-4'],
        ]);
        CRUD::addField([
            'name' => 'hijri_year',
            'label' => trans('course.hijri_year'),
            'wrapper' => ['class' => 'form-group col-md-4'],
        ]);
        CRUD::addField([
            'name' => 'semester',
            'label' => trans('course.semester'),
            'wrapper' => ['class' => 'form-group col-md-4'],
        ]);
        CRUD::addField([
            'name' => 'duration',
            'label' => trans('course.duration'),
        ]);


        CRUD::addField([
            'name' => 'start_date',
            'label' => trans('course.start_date'),
            'type'        => 'date',
            'wrapper' => ['class' => 'form-group col-md-6'],

        ]);
        CRUD::addField([
            'name' => 'end_date',
            'label' => trans('course.end_date'),
            'type'        => 'date',
            'wrapper' => ['class' => 'form-group col-md-6'],

        ]);


        CRUD::addField(
            [   // 1-n relationship
                'label'       => trans('course.course_root_id'), // Table column heading
                'type'        => "select2_from_ajax",
                'name'        => 'course_root_id', // the column that contains the ID of that connected entity
                'entity'      => 'courseRootId', // the method that defines the relationship in your Model
                'attribute'   => "long_name", // foreign key attribute that is shown to user
                'data_source' => url("api/course"), // url to controller search function (with /{id} should return model)

                // OPTIONAL
                'placeholder'             => "Select a course", // placeholder for the select
                'minimum_input_length'    => 1, // minimum characters to type before querying results
                // 'model'                   => "App\Models\Category", // foreign key model
                // 'dependencies'            => ['category'], // when a dependency changes, this select2 is reset to null
                // 'method'                  => 'GET', // optional - HTTP method to use for the AJAX call (GET, POST)
                // 'include_all_form_fields' => false, // optional - only send the current field through AJAX (for a smaller payload if you're not using multiple chained select2s)
                'tab'   => trans('course.course_root_id'),

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


    public function fetchAcademicpath()
    {
        // return $this->fetch(\App\Models\academicpath::class);

        return $this->fetch([
            'model' => \App\Models\AcademicPath::class, // required
            'searchable_attributes' => ['academic_path_name'],
            'paginate' => 10, // items to show per page
            // 'query' => function ($model) {
            //     return $model->active();
            // } // to filter the results that are returned
        ]);
    }


    public function clone($id)
    {
        $this->crud->hasAccessOrFail('clone');
        $this->crud->setOperation('clone');

        // Begin a database transaction
        DB::beginTransaction();

        try {
            // Find the course to clone
            $course = \App\Models\Course::with('classRooms')->findOrFail($id);

            // Duplicate the course
            $clonedCourse = $course->replicate();
            $clonedCourse->course_year = $course->course_year . ' (Copy)';
            $clonedCourse->is_active = false; // Default to inactive
            $clonedCourse->save();

            // Clone all related classRooms
            foreach ($course->classRooms as $classRoom) {
                $clonedClassRoom = $classRoom->cloneClassRoomWithStudents();
                $clonedClassRoom->course_id = $clonedCourse->id; // Associate with the cloned course
                $clonedClassRoom->save();
            }

            // Commit the transaction
            DB::commit();

            // Redirect back with a success message
            return redirect()->back()->with('success', trans('course.clone_success'));
        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();

            // Log the error for debugging
            // Log::error('Error cloning course: ' . $e->getMessage());

            // Redirect back with an error message
            return redirect()->back()->withErrors(['error' => trans('course.clone_failed')]);
        }
    }
}
