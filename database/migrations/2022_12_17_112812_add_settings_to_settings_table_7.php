<?php

use Backpack\Settings\app\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSettingsToSettingsTable7 extends Migration
{




    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        /**
         * The settings to add.
         */
        $settings = [];




        $reports_list = [

            'classes_and_students_statistics_for_each_level',
            'student_detection_statistics_in_classes',
            'statistics_for_the_number_of_students_in_classes',
            'study_group_data_disclosure_statistics',
            'student_list_statistics',
            'student_list_statistics_for_each_grade',
            'statistics_of_students_scores_according_to_grades',
            'class_average_score_statistics',

        ];

        foreach ($reports_list as $key) {
            $settings = array_merge($settings, [
                [
                    'key'         => $key . '.title',
                    'name'        => trans('reports.title') . ':' . trans('reports.' . $key),
                    'description' => '',
                    'value'       => '',
                    'field'       => '{"name":"value","label":"Value","type":"text"}',
                    'active'      => 1,
                ],
                [
                    'key'         => $key . '.pre',
                    'name'        => trans('reports.pre') . ':' . trans('reports.' . $key),
                    'description' => '',
                    'value'       => '',
                    'field'       => '{"name":"value","label":"Value","type":"tinymce"}',
                    'active'      => 1,
                ],
                [
                    'key'         => $key . '.pro',
                    'name'        => trans('reports.pro') . ':' . trans('reports.' . $key),
                    'description' => '',
                    'value'       => '',
                    'field'       => '{"name":"value","label":"Value","type":"tinymce"}',
                    'active'      => 1,
                ]
            ]);
        }








        foreach (array_reverse($settings) as $index => $setting) {
            $result = DB::table('settings')->insert($setting);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
