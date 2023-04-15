<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\CurriculumRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class CurriculumCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CurriculumCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\FetchOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Curriculum::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/curriculum');
        CRUD::setEntityNameStrings(trans('curriculum.curriculum'), trans('curriculum.curricula'));
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
            'name' => 'short_name',
            'label' => trans('curriculum.short_name'),
        ]);
        CRUD::addColumn([
            'name' => 'curriculum_name',
            'label' => trans('curriculum.curriculum_name'),
        ]);

        CRUD::addColumn([
            'name' => 'book_name',
            'label' => trans('curriculum.book_name'),
        ]);

        CRUD::addColumn([
            'name' => 'weight_in_hours',
            'label' => trans('curriculum.weight_in_hours'),
        ]);


        CRUD::addColumn([
            // any type of relationship
            'name'         => 'curriculumCategory', // name of relationship method in the model
            'type'         => 'relationship',
            'label'        => trans('curriculumcategory.curriculumcategory'), // Table column heading
            // OPTIONAL
            // 'entity'    => 'tags', // the method that defines the relationship in your Model
            // 'attribute' => 'name', // foreign key attribute that is shown to user
            // 'model'     => App\Models\Category::class, // foreign key model
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhereHas('curriculumCategory', function ($query) use ($column, $searchTerm) {
                    $query->where('category_name', 'like', '%' . $searchTerm . '%');
                });
            }
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
        CRUD::setValidation(CurriculumRequest::class);


        CRUD::addField([
            'name' => 'curriculum_name',
            'label' => trans('curriculum.curriculum_name'),
        ]);

        CRUD::addField([
            'name' => 'short_name',
            'label' => trans('curriculum.short_name'),
        ]);

        CRUD::addField([
            'name' => 'book_name',
            'label' => trans('curriculum.book_name'),
        ]);

        CRUD::addField([
            'name' => 'weight_in_hours',
            'label' => trans('curriculum.weight_in_hours'),
        ]);

        CRUD::addField([
            'name' => 'weight_in_hours',
            'label' => trans('curriculum.weight_in_hours'),
        ]);

        CRUD::addField(
            [
                // relationship
                'type' => "relationship",
                'name' => 'curriculum_category_id', // the method on your model that defines the relationship
                'ajax' => true,
                'inline_create' => true,
                'inline_create' => [
                    'entity' => 'curriculumcategory',
                    // 'force_select' => true, // should the inline-created entry be immediately selected?
                    // 'modal_class' => 'modal-dialog modal-xl', // use modal-sm, modal-lg to change width
                    // 'modal_route' => route('curriculumCategory-inline-create'), // InlineCreate::getInlineCreateModal()
                    // 'create_route' =>  route('curriculumCategory-inline-create-save'), // InlineCreate::storeInlineCreate()
                    // 'include_main_form_fields' => ['field1', 'field2'], // pass certain fields from the main form to the modal

                ],
                // OPTIONALS:
                'label' => trans('curriculumcategory.curriculumcategory'),
                'attribute' => "category_name", // foreign key attribute that is shown to user (identifiable attribute)
                'entity' => 'curriculumCategory', // the method that defines the relationship in your Model
                // 'model' => "App\Models\Category", // foreign key Eloquent model
                'placeholder' => "Select a Category", // placeholder for the select2 input

                // AJAX OPTIONALS:
                'data_source' => backpack_url("curriculum/fetch/curriculumcategory"), // url to controller search function (with /{id} should return model)
                // 'minimum_input_length' => 2, // minimum characters to type before querying results
                // 'dependencies'         => ['curriculumcategory'], // when a dependency changes, this select2 is reset to null
                // 'include_all_form_fields'  => true, // optional - only send the current field through AJAX (for a smaller payload if you're not using multiple chained select2s)
            ]
        );

        CRUD::addField([   // repeatable
            'name'  => 'marks_labels',
            'label' => trans('studentmark.marks'),
            'type'  => 'repeatable',

            // optional
            'new_item_label'  => trans('curriculum.curriculum_marks_sheet'), // customize the text of the button
            'init_rows' => 0, // number of empty rows to be initialized, by default 1
            'min_rows' => 0, // minimum rows allowed, when reached the "delete" buttons will be hidden
            'max_rows' => 1, // maximum rows allowed, when reached the "new item" button will be hidden

            'default' => '[{"finalexam_mark_details":"[{\"label\":\"درجة النهائي\",\"mark\":\"30\"}]","midexam_marks_details":"[{\"label\":\"المنتصف\",\"mark\":\"30\"}]","class_mark_details":"[{\"label\":\"درجة الاعمال\",\"mark\":\"30\"}]","attend_mark_details":"[{\"label\":\"الحضور والانضباط\",\"mark\":\"10\"}]","marks_details":"[{\"label\":\"الأداء العملي\",\"mark\":\"10\"},{\"label\":\"اختبار قصير1\",\"mark\":\"5\"},{\"label\":\"نشاط غير صفي\",\"mark\":\"10\"},{\"label\":\"اختبار قصير2\",\"mark\":\"5\"}]","project_marks_details":"","practice_mark_details":"","memorize_mark_details":""}]',

            'fields' => [

                [   // Table
                    'name'            => 'finalexam_mark_details',
                    'label'           => trans('studentmark.finalexam_mark_details'),
                    'type'            => 'table',

                    'entity_singular' => '', // used on the "Add X" button
                    'columns'         => [
                        'label'  => trans('studentmark.marks_details_label'),
                        'mark'  => trans('curriculum.full_mark'),
                    ],
                    'max' => 2, // maximum rows allowed in the table
                    'min' => 0, // minimum rows allowed in the table
                ],

                [   // Table
                    'name'            => 'midexam_marks_details',
                    'label'           => trans('studentmark.midexam_marks_details'),
                    'type'            => 'table',
                    'entity_singular' => '', // used on the "Add X" button
                    'columns'         => [
                        'label'  => trans('studentmark.marks_details_label'),
                        'mark'  => trans('curriculum.full_mark'),
                    ],
                    'max' => 20, // maximum rows allowed in the table
                    'min' => 0, // minimum rows allowed in the table
                ],

                [   // Table
                    'name'            => 'class_mark_details',
                    'label'           => trans('studentmark.class_mark_details'),
                    'type'            => 'table',
                    'entity_singular' => '', // used on the "Add X" button
                    'columns'         => [
                        'label'  => trans('studentmark.marks_details_label'),
                        'mark'  => trans('curriculum.full_mark'),
                    ],
                    'max' => 1, // maximum rows allowed in the table
                    'min' => 0, // minimum rows allowed in the table
                ],

                [   // Table
                    'name'            => 'attend_mark_details',
                    'label'           => trans('studentmark.attend_mark_details'),
                    'type'            => 'table',
                    'entity_singular' => '', // used on the "Add X" button
                    'columns'         => [
                        'label'  => trans('studentmark.marks_details_label'),
                        'mark'  => trans('curriculum.full_mark'),
                    ],
                    'max' => 1, // maximum rows allowed in the table
                    'min' => 0, // minimum rows allowed in the table
                ],

                [   // Table
                    'name'            => 'marks_details',
                    'label'           => trans('studentmark.marks_details'),
                    'type'            => 'table',
                    'entity_singular' => '', // used on the "Add X" button
                    'columns'         => [
                        'label'  => trans('studentmark.marks_details_label'),
                        'mark'  => trans('curriculum.full_mark'),
                    ],
                    'max' => 20, // maximum rows allowed in the table
                    'min' => 0, // minimum rows allowed in the table
                ],

                [   // Table
                    'name'            => 'project_marks_details',
                    'label'           => trans('studentmark.project_marks_details'),
                    'type'            => 'table',
                    'entity_singular' => '', // used on the "Add X" button
                    'columns'         => [
                        'label'  => trans('studentmark.marks_details_label'),
                        'mark'  => trans('curriculum.full_mark'),
                    ],
                    'max' => 20, // maximum rows allowed in the table
                    'min' => 0, // minimum rows allowed in the table
                ],

                [   // Table
                    'name'            => 'practice_mark_details',
                    'label'           => trans('studentmark.practice_mark_details'),
                    'type'            => 'table',
                    'entity_singular' => '', // used on the "Add X" button
                    'columns'         => [
                        'label'  => trans('studentmark.marks_details_label'),
                        'mark'  => trans('curriculum.full_mark'),
                    ],
                    'max' => 20, // maximum rows allowed in the table
                    'min' => 0, // minimum rows allowed in the table
                ],

                [   // Table
                    'name'            => 'memorize_mark_details',
                    'label'           => trans('studentmark.memorize_mark_details'),
                    'type'            => 'table',
                    'entity_singular' => '', // used on the "Add X" button
                    'columns'         => [
                        'label'  => trans('studentmark.marks_details_label'),
                        'mark'  => trans('curriculum.full_mark'),
                    ],
                    'max' => 20, // maximum rows allowed in the table
                    'min' => 0, // minimum rows allowed in the table
                ],


            ],

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


    public function fetchCurriculumcategory()
    {
        // return $this->fetch(\App\Models\curriculumcategory::class);

        return $this->fetch([
            'model' => \App\Models\CurriculumCategory::class, // required
            'searchable_attributes' => ['category_name'],
            'paginate' => 10, // items to show per page
            // 'query' => function ($model) {
            //     return $model->active();
            // } // to filter the results that are returned
        ]);
    }
}
