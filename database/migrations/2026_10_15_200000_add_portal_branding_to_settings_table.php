<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backpack settings for the new panel's texts (PortalBranding): portal name, short name, centre name and
 * description. Shown in the portal, the installed app (manifest), the page title and push notifications.
 */
class AddPortalBrandingToSettingsTable extends Migration
{
    const SETTINGS = [
        'portal.name' => [
            'name' => 'اسم البوابة',
            'description' => 'يظهر في عنوان الصفحة وعند تثبيت البوابة على الهاتف.',
            'value' => 'بوابة الغدير',
        ],
        'portal.short_name' => [
            'name' => 'الاسم المختصر للبوابة',
            'description' => 'يظهر تحت أيقونة البوابة على الشاشة الرئيسية للهاتف. يفضّل ألا يتجاوز 12 حرفًا.',
            'value' => 'الغدير',
        ],
        'portal.centre' => [
            'name' => 'اسم الجمعية في البوابة',
            'description' => 'يظهر في البوابة الجديدة وفي الإيصالات وعنوانًا لإشعارات الرسائل.',
            'value' => 'جمعية الغدير لعلوم القرآن',
        ],
        'portal.description' => [
            'name' => 'وصف البوابة',
            'description' => 'وصف قصير يظهر عند تثبيت البوابة على الهاتف.',
            'value' => 'بوابة الطالب — جمعية الغدير لعلوم القرآن',
        ],
    ];

    public function up()
    {
        foreach (self::SETTINGS as $key => $setting) {
            if (DB::table('settings')->where('key', $key)->exists()) {
                continue;
            }

            DB::table('settings')->insert($setting + [
                'key' => $key,
                'field' => json_encode(['name' => 'value', 'label' => 'Value', 'type' => 'text'], JSON_UNESCAPED_UNICODE),
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        DB::table('settings')->whereIn('key', array_keys(self::SETTINGS))->delete();
    }
}
