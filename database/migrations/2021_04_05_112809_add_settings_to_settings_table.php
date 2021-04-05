<?php

use Backpack\Settings\app\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSettingsToSettingsTable extends Migration
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


        // main settings

        $settings = array_merge($settings, [
            [
                'key'         => 'organization_name',
                'name'        => 'اسم المؤسسة',
                'description' => '',
                'value'       => 'تعليم الصلاة',
                'field'       => '{"name":"value","label":"Value","type":"text"}',
                'active'      => 1,
            ],
            [
                'key'         => 'print_header',
                'name'        => 'هيدر الطباعة',
                'description' => '',
                'value'       => '',
                'field'       => '{"name":"value","label":"Value","type":"browse"}',
                'active'      => 1,
            ],
        ]);



        $reports_list = [
            'scoring_sheet',
            'student_attend_list',
            'student_attend_report',
            'student_attend',
            'student_courses_transcript',
            'student_edu_statement',
            'transcript',
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
        Schema::table('settings', function (Blueprint $table) {
            Setting::truncate();
        });
    }
}
