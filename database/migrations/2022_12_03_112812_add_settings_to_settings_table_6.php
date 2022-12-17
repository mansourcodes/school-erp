<?php

use Backpack\Settings\app\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSettingsToSettingsTable6 extends Migration
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
            'balance_statement_report',
            'detecting_helpers_report',
            'list_of_assistance_students_who_participated_in_the_payment_report',
            'list_of_unconfirmed_students_report',
            'list_of_non_paying_students_report',
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
