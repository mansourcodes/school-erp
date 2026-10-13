<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backpack setting `portal.mode` (PLAN.md §4, Q7): beta / live / maintenance / closed.
 */
class AddPortalModeToSettingsTable extends Migration
{
    public function up()
    {
        if (DB::table('settings')->where('key', 'portal.mode')->exists()) {
            return;
        }

        DB::table('settings')->insert([
            'key' => 'portal.mode',
            'name' => 'وضع بوابة الطالب الجديدة',
            'description' => 'تجريبي: البوابة القديمة هي الافتراضية والجديدة على /beta. مباشر: تُحوَّل البوابة القديمة إلى الجديدة (بعد تحديث المنصة فقط). صيانة / مغلقة: تظهر رسالة للطلاب. لوحة الإدارة لا تتأثر.',
            'value' => 'beta',
            'field' => json_encode([
                'name' => 'value',
                'label' => 'Value',
                'type' => 'select_from_array',
                'options' => [
                    'beta' => 'تجريبي (beta)',
                    'live' => 'مباشر (live)',
                    'maintenance' => 'صيانة (maintenance)',
                    'closed' => 'مغلقة (closed)',
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
        DB::table('settings')->where('key', 'portal.mode')->delete();
    }
}
