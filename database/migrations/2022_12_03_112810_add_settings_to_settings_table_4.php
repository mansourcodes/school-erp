<?php

use Backpack\Settings\app\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSettingsToSettingsTable4 extends Migration
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




        $settings = array_merge($settings, [
            [
                'key'         => 'ceo_name',
                'name'        =>  trans('reports.ceo_name'),
                'description' => '',
                'value'       => '',
                'field'       => '{"name":"value","label":"Value","type":"text"}',
                'active'      => 1,
            ],

        ]);



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
