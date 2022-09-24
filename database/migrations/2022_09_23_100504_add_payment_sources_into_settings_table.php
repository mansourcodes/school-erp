<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddPaymentSourcesIntoSettingsTable extends Migration
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
        $settings = [
            [
                'key'         => 'payment_types',
                'name'        => 'payment_types',
                'description' => '',
                'value'       => '[{"key":"cash"},{"key":"benefit pay"}]',
                'field'       => '{ "name":"value", "label":"Value", "type":"table", "entity_singular":"Payment type", "columns":{"key":"Key"}, "max":"5", "min":"0" }',
                'active'      => 1,

            ],
            [
                'key'         => 'site_name',
                'name'        => 'site_name',
                'description' => '',
                'value'       => '',
                'field'       => '{ "name":"value", "label":"Value", "type":"text"}',
                'active'      => 1,

            ],
            [
                'key'         => 'site_offline',
                'name'        => 'site_offline',
                'description' => '',
                'value'       => '0',
                'field'       => '{ "name":"value", "label":"Value", "type":"checkbox"}',
                'active'      => 1,

            ],
            [
                'key'         => 'meta_desc',
                'name'        => 'meta_desc',
                'description' => '',
                'value'       => '',
                'field'       => '{ "name":"value", "label":"Value", "type":"text"}',
                'active'      => 1,

            ],
            [
                'key'         => 'meta_keys',
                'name'        => 'meta_keys',
                'description' => '',
                'value'       => '',
                'field'       => '{ "name":"value", "label":"Value", "type":"text"}',
                'active'      => 1,

            ],
        ];




        foreach ($settings as $index => $setting) {
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
        //
    }
}
