<?php

use Backpack\Settings\app\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EditPaymentTypeMaxOnSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $payment_types_settings = Setting::where('key', 'payment_types')->first();

        $payment_types_settings->field = '{ "name":"value", "label":"Value", "type":"table", "entity_singular":"Payment type", "columns":{"key":"Key"}, "max":"50", "min":"0" }';
        $payment_types_settings->save();
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
