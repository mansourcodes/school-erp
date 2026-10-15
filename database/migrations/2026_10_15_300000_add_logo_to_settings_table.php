<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backpack setting `logo` (AdminBranding): an uploaded image on the public disk, shown on the admin login
 * page and as the admin navbar brand. Empty means the text project name is shown instead.
 */
class AddLogoToSettingsTable extends Migration
{
    public function up()
    {
        if (DB::table('settings')->where('key', 'logo')->exists()) {
            return;
        }

        DB::table('settings')->insert([
            'key' => 'logo',
            'name' => 'الشعار',
            'description' => 'يظهر في صفحة دخول لوحة الإدارة وفي أعلى القائمة. PNG أو JPG أو WebP، حتى 2 ميغابايت.',
            'value' => '',
            'field' => json_encode([
                'name' => 'value',
                'label' => 'الشعار',
                'type' => 'upload',
                'upload' => true,
                'disk' => 'public',
            ], JSON_UNESCAPED_UNICODE),
            'active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        DB::table('settings')->where('key', 'logo')->delete();
    }
}
