<?php

use Backpack\Settings\app\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSettingsToSettingsTable5 extends Migration
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
            'parent_meeting_template',
            'registration_form',
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
