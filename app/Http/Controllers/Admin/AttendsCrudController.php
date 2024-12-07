<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Http\Requests\AttendsRequest;
use App\Models\Attends;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\Curriculum;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Http\Request;
use stdClass;
use Backpack\CRUD\app\Library\Widget;
use Alert;

/**
 * Class AttendsCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class AttendsCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\BulkDeleteOperation {
        bulkDelete as traitBulkDelete;
    }



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
        $this->crud->enableExportButtons();

        // CRUD::column('date');
        // CRUD::column('start_time');
        // CRUD::column('class_room_id');

        CRUD::addColumn(
            [
                'name'        => 'date',
                'label'       => trans('base.date'),
                'type'        => 'date',
            ]
        );


        CRUD::addColumn(
            [
                'name'        => 'start_time',
                'label'       => trans('classroom.start_time'),
                'type'  => 'datetime',
                'format' => 'H:MM a'
            ]
        );

        /*
        CRUD::addColumn(
            [
                // any type of relationship
                'name'         => 'curriculum', // name of relationship method in the model
                'type'         => 'relationship',
                'label'        => trans('curriculum.curriculum'), // Table column heading
                // OPTIONAL
                // 'entity'    => 'tags', // the method that defines the relationship in your Model
                'attribute' => 'long_name', // foreign key attribute that is shown to user
                // 'model'     => App\Models\Category::class, // foreign key model
            ]
        );
        //*/

        CRUD::addColumn(
            [
                // any type of relationship
                'name'         => 'classRoom', // name of relationship method in the model
                'type'         => 'relationship',
                'label'        => trans('classroom.classroom'), // Table column heading
                // OPTIONAL
                // 'entity'    => 'tags', // the method that defines the relationship in your Model
                'attribute' => 'long_name', // foreign key attribute that is shown to user
                // 'model'     => App\Models\Category::class, // foreign key model
            ]
        );

        CRUD::addColumn(
            [
                // any type of relationship
                'name'         => 'course', // name of relationship method in the model
                'type'         => 'relationship',
                'label'        => trans('course.course'), // Table column heading
                // OPTIONAL
                // 'entity'    => 'tags', // the method that defines the relationship in your Model
                'attribute' => 'long_name', // foreign key attribute that is shown to user
                // 'model'     => App\Models\Category::class, // foreign key model
            ]
        );



        // select2_ajax filter
        $this->crud->addFilter(
            [
                'name'        => 'curriculum_filter',
                'type'        => 'select2_ajax',
                'label'       => trans('curriculum.curriculum'),
                'placeholder' => trans('curriculum.curriculum')
            ],
            url('api/curriculum'), // the ajax route
            function ($value) { // if the filter is active
                $this->crud->addClause('where', 'curriculum_id', $value);
            }
        );

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

        // select2_ajax filter
        $this->crud->addFilter(
            [
                'name'        => 'classroom_filter',
                'type'        => 'select2_ajax',
                'label'       => trans('classroom.classroom'),
                'placeholder' => trans('classroom.classroom')
            ],
            url('api/classroom'), // the ajax route
            function ($value) { // if the filter is active
                $this->crud->addClause('where', 'class_room_id', $value);
            }
        );


        // daterange filter
        $this->crud->addFilter(
            [
                'type'  => 'date_range',
                'name'  => 'from_to',
                'label' => trans('base.date')
            ],
            false,
            function ($value) { // if the filter is active, apply these constraints
                $dates = json_decode($value);

                function convert($string)
                {
                    $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
                    $arabic = ['٩', '٨', '٧', '٦', '٥', '٤', '٣', '٢', '١', '٠'];
                    $num = ['9', '8', '7', '6', '5', '4', '3', '2', '1', '0'];
                    $convertedPersianNums = str_replace($persian, $num, $string);
                    $englishNumbersOnly = str_replace($arabic, $num, $convertedPersianNums);

                    return $englishNumbersOnly;
                }


                $this->crud->addClause('where', 'date', '>=', convert($dates->from));
                $this->crud->addClause('where', 'date', '<=', convert($dates->to) . ' 23:59:59');
            }
        );


        $this->crud->addFilter(
            [
                'name'       => 'number',
                'type'       => 'range',
                'label'      => trans('base.hour'),
                'label_from' => 'from',
                'label_to'   => 'to'
            ],
            false,
            function ($value) { // if the filter is active
                $range = json_decode($value);
                if ($range->from) {
                    $this->crud->addClause('where', 'start_time', '>=', $range->from . ':00:00');
                }
                if ($range->to) {
                    $this->crud->addClause('where', 'start_time', '<=', $range->to . ':59:59');
                }
            }
        );


        $this->crud->addFilter([
            'name'  => 'show_all',
            'type'  => 'select2',
            'label' => trans('base.show_all'),
        ], function () {
            return [
                1 => trans('base.show_all'),
            ];
        }, function ($value) {});

        $is_filter_active = false;
        $filters = $this->crud->filters();
        foreach ($filters as $key => $singleFilter) {
            if ($singleFilter->currentValue !== null) {
                $is_filter_active = true;
            }
        }
        if (!$is_filter_active) {
            $this->crud->addClause('whereHas', 'course', function ($query) {
                $query->where('is_active', true);
            });
        }

        //*/

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
            [   // 1-n relationship
                'label'       => trans('curriculum.curriculum'), // Table column heading
                'type'        => "select2_from_ajax",
                'name'        => 'curriculum_id', // the column that contains the ID of that connected entity
                'entity'      => 'curriculum', // the method that defines the relationship in your Model
                'attribute'   => "long_name", // foreign key attribute that is shown to user
                'data_source' => url("api/curriculum"), // url to controller search function (with /{id} should return model)

                // OPTIONAL
                'placeholder'             => "Select a curriculum", // placeholder for the select
                'minimum_input_length'    => 1, // minimum characters to type before querying results
                // 'model'                   => "App\Models\Category", // foreign key model
                // 'dependencies'            => ['category'], // when a dependency changes, this select2 is reset to null
                // 'method'                  => 'GET', // optional - HTTP method to use for the AJAX call (GET, POST)
                // 'include_all_form_fields' => false, // optional - only send the current field through AJAX (for a smaller payload if you're not using multiple chained select2s)
                'attributes' => [
                    // 'readonly'    => 'readonly',

                ],
                'wrapper' => ['class' => 'form-group col-md-4'],
            ],
        );

        CRUD::addField(
            [
                'name'        => 'date',
                'label'       => trans('base.date'),
                'type'        => 'date',
                'attributes' => [
                    'readonly'    => 'readonly',

                ],
                'wrapper' => ['class' => 'form-group col-md-4'],
            ]
        );

        CRUD::addField(
            [
                'name'        => 'start_time',
                'label'       => trans('classroom.start_time'),
                'type'        => 'time',
                'attributes' => [
                    'readonly'    => 'readonly',

                ],
                'wrapper' => ['class' => 'form-group col-md-4'],
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
                'attributes' => [
                    'readonly'    => 'readonly',

                ],
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
                'attributes' => [
                    'readonly'    => 'readonly',

                ],
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
            'tab'   => trans('attend.attend_students'),

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
        $currentAttends = Attends::find($this->crud->getCurrentEntryId());

        $currentAttends->date;
        $widget_definition_array = [
            'type'       => 'card',
            'wrapper' => ['class' => 'col-md-6'], // optional
            'class'   => 'card border-danger', // optional
            'content'    => [
                'header' => trans('base.notice'), // optional
                'body'   => trans('attend.edit_attends_notice') . ' <br> <a href="' . backpack_url('quick_delete_and_add?id=' . $currentAttends->id) . '" class="btn btn-sm btn-primary float-left"> <i class="las la-trash-alt"></i>' . trans('attend.quick_delete_and_add') . '</a>',
            ]
        ];

        Widget::add($widget_definition_array)->to('before_content');
    }




    public function addAttendByDate(Request $request)
    {
        $chosen_date = Carbon::parse($request->get('chosen_date'));
        $data['chosen_date'] = $chosen_date->format('Y-m-d');
        $data['chosen_date_long'] = $chosen_date->locale('ar')->dayName . $chosen_date->format(' d ') . $chosen_date->locale('ar')->monthName . $chosen_date->format(' Y');
        $data['breadcrumbs'] = [
            trans('backpack::crud.admin') => backpack_url('dashboard'),
            trans('attend.add_attend_w_date') => false,
        ];

        $data['classrooms'] = ClassRoom::getByDate($chosen_date);

        // foreach ($data['classrooms'] as $class) {

        //     foreach ($class->active_attend_table as $key => $value) {

        //         if (!$value['curriculum']) {
        //             dd($value, $class);
        //         }

        //         // dd($value['curriculum']->long_name);
        //     }
        // }
        // dd(1);

        return view('attend.attend_list', $data);
    }


    public function AttendEasyForm(Request $request)
    {

        $date = $request->get('date');
        $start_time = $request->get('start_time');
        $class_room_id = $request->get('class_room_id');
        // $course_id = $request->get('course_id');
        $curriculum_id = $request->get('curriculum_id');

        $data = [
            'date' => Carbon::parse($date),
            'start_time' => Carbon::parse($start_time),
            'classroom' => ClassRoom::find($class_room_id),
            // 'course' => Course::find($course_id),
            'curriculum' => Curriculum::find($curriculum_id),
        ];

        return view('attend.add_attend_easy_form', $data);
    }


    public function SaveAttendEasyForm(Request $request)
    {
        $chosen_date = Carbon::parse($request->get('date'));


        $attend = new Attends();

        $attend->date = $request->get('date');
        $attend->start_time = $request->get('start_time');
        $attend->class_room_id = $request->get('class_room_id');
        $attend->course_id = $request->get('course_id');
        $attend->curriculum_id = $request->get('curriculum_id');
        $student_attend_state = $request->get('student_attend_state');

        $attend_state = [];

        $attend_state['attend'] = [];
        $attend_state['absent'] = [];
        $attend_state['absent_w_excuse'] = [];
        $attend_state['late'] = [];
        $attend_state['late_w_excuse'] = [];

        foreach ($student_attend_state as $key => $value) {
            $attend_state[$value][] = $key;
        }


        try {
            $attend->save();
        } catch (\Exception $e) {
            \Alert::add('error', trans('attend.attend_added_fail'))->flash();

            if (str_contains($e->getMessage(), 'attends_date_start_time_class_room_id_curriculum_id_unique')) {
                \Alert::add('info', trans('attend.attend_exist'))->flash();
            }

            return redirect('/admin/add_attend_by_date?chosen_date=' . $chosen_date->format('Y-m-d'));
        }


        $attend->attendStudents()->sync($attend_state['attend']);
        $attend->absentStudents()->sync($attend_state['absent']);
        $attend->absentWithExcuseStudents()->sync($attend_state['absent_w_excuse']);
        $attend->lateStudents()->sync($attend_state['late']);
        $attend->lateWithExcuseStudents()->sync($attend_state['late_w_excuse']);

        $attend->update();

        return redirect('/admin/add_attend_by_date?chosen_date=' . $chosen_date->format('Y-m-d'))
            ->with([
                'status' => trans('attend.attend_added_successfuly'),
                'attend_code_added' => $attend->code,
            ]);
    }

    public function QuickDeleteAndAdd(Request $request)
    {

        $id = $request->get('id');
        $attend = Attends::find($id);
        $url = '/admin/attend_easy_form?date=' . $attend->date . '&start_time=' . $attend->start_time . '&curriculum_id=' . $attend->curriculum_id . '&class_room_id=' . $attend->class_room_id . '&course_id=' . $attend->course_id . '';
        $code = $attend->code;




        $attend->delete();

        \Alert::add('info', trans('attend.attend_deleted_successfuly'))->flash();
        \Alert::add('light', trans('attend.now_add_new_attend'))->flash();

        return redirect($url);
    }
}
