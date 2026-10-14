<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backpack setting `portal.theme`: colour theme of the new panel for every student and teacher.
 */
class AddPortalThemeToSettingsTable extends Migration
{
    public function up()
    {
        if (DB::table('settings')->where('key', 'portal.theme')->exists()) {
            return;
        }

        DB::table('settings')->insert([
            'key' => 'portal.theme',
            'name' => 'مظهر البوابة الجديدة',
            'description' => 'ألوان البوابة الجديدة (/beta) لكل الطلاب والمعلمين. يظهر التغيير عند فتح البوابة أو تحديث الصفحة.',
            'value' => 'ghadeer',
            'field' => json_encode([
                'name' => 'value',
                'label' => 'Value',
                'type' => 'select_from_array',
                'options' => [
                    'ghadeer' => 'الغدير الفاتح (الافتراضي)',
                    'girls' => 'إسلامي للبنات',
                    'cupcake' => 'Cupcake',
                    'autumn' => 'Autumn',
                    'wireframe' => 'Wireframe',
                ],
                'allows_null' => false,
            ], JSON_UNESCAPED_UNICODE),
            'active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        DB::table('settings')->where('key', 'portal.theme')->delete();
    }
}
