<?php

use App\Support\AdminTheme;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backpack setting `admin.theme` (AdminTheme): colour theme of the admin panel for every admin user.
 */
class AddAdminThemeToSettingsTable extends Migration
{
    public function up()
    {
        if (DB::table('settings')->where('key', 'admin.theme')->exists()) {
            return;
        }

        DB::table('settings')->insert([
            'key' => 'admin.theme',
            'name' => 'مظهر لوحة الإدارة',
            'description' => 'ألوان لوحة الإدارة لكل المستخدمين. يظهر التغيير عند تحديث الصفحة.',
            'value' => AdminTheme::DEFAULT,
            'field' => json_encode([
                'name' => 'value',
                'label' => 'Value',
                'type' => 'select_from_array',
                'options' => AdminTheme::ALL,
                'allows_null' => false,
            ], JSON_UNESCAPED_UNICODE),
            'active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        DB::table('settings')->where('key', 'admin.theme')->delete();
    }
}
