<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\PaymentRequest;
use App\Models\Account\Payment;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class PaymentCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class PaymentCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(Payment::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/payment');
        CRUD::setEntityNameStrings(trans('account.payment'), trans('account.payments'));
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
            'name' => 'student_id',
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

        CRUD::addColumn(
            [
                'name'    => 'amount',
                'suffix'    => ' دب',
                'label'   => trans('account.amount'),

            ]
        );

        CRUD::addColumn(
            [
                'name'    => 'type',
                'label'   => trans('account.payment_type'),

            ]
        );

        CRUD::addColumn(
            [
                'name'    => 'source',
                'label'   => trans('account.payment_source'),

            ]
        );


        // CRUD::setFromDb(); // columns

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']); 
         */


        $this->crud->addButtonFromModelFunction('line', 'get_print_button', 'getPrintButton', 'beginning');
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(PaymentRequest::class);


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
                'wrapper'   => [
                    'class'      => 'form-group col-6'
                ],
                'default'   => request()->student,
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
                'wrapper'   => [
                    'class'      => 'form-group col-6'
                ],
                'default'   => request()->course,
            ],
        );


        CRUD::addField(
            [   // select2_from_array
                'name'        => 'source',
                'label'       => trans('account.payment_source'), // Table column heading
                'type'        => 'select2_from_array',
                'options'     => getPaymentSourceArray(),
                'allows_null' => false,
                'default'     => 'CASH',
                // 'allows_multiple' => true, // OPTIONAL; needs you to cast this to array in your model;
                'wrapper'   => [
                    'class'      => 'form-group col-4'
                ],
            ],
        );

        CRUD::addField(
            [   // select2_from_array
                'name'        => 'type',
                'label'       => trans('account.payment_type'), // Table column heading
                'type'        => 'select2_from_array',
                'options'     => getPaymentTypesArray(),
                'allows_null' => false,
                'default'     => 'Full',
                // 'allows_multiple' => true, // OPTIONAL; needs you to cast this to array in your model;
                'wrapper'   => [
                    'class'      => 'form-group col-4'
                ],
            ],
        );

        CRUD::addField(
            [   // select2_from_array
                'name'        => 'amount',
                'label'       => trans('account.amount'), // Table column heading
                'type'        => 'number',
                'default'     => '25',

                // optionals
                'attributes' => ["step" => "any"], // allow decimals
                'prefix'     => " دب ",
                // 'suffix'     => ".00",

                'wrapper'   => [
                    'class'      => 'form-group col-4'
                ],
            ],
        );

        CRUD::field('meta')->label(trans('account.meta'));

        // CRUD::setFromDb(); // fields

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
