<?php

use Backpack\Settings\app\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSettingsToSettingsTable extends Migration
{

    /**
     * The settings to add.
     */
    protected $settings = [
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
            'field'       => '{"name":"value","label":"Value","type":"file"}',
            'active'      => 1,

        ],

    ];


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->settings as $index => $setting) {
            $result = DB::table('settings')->insert($setting);

            if (!$result) {
                $this->command->info("Insert failed at record $index.");

                return;
            }
        }

        // $this->command->info('Inserted ' . count($this->settings) . ' records.');
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
