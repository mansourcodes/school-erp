<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddPaymentTypesIntoSettingsTable extends Migration
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
                'key'         => 'payment_sources',
                'name'        => 'payment_sources',
                'description' => '',
                'value'       => '[{"key":"cash"},{"key":"benefit pay"}]',
                'field'       => '{ "name":"value", "label":"Value", "type":"table", "entity_singular":"Payment type", "columns":{"key":"Key"}, "max":"50", "min":"0" }',
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
