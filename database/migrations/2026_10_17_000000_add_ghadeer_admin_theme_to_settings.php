<?php

use App\Support\AdminTheme;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds the `ghadeer` theme (الغدير: the /beta portal colours, _brand/new_admin_design) to the `admin.theme`
 * options and makes it the active admin theme. `down()` goes back to `default` and drops the option.
 */
class AddGhadeerAdminThemeToSettings extends Migration
{
    public function up()
    {
        $this->syncOptions();

        DB::table('settings')->where('key', 'admin.theme')
            ->update(['value' => 'ghadeer', 'updated_at' => now()]);
    }

    public function down()
    {
        DB::table('settings')->where('key', 'admin.theme')->where('value', 'ghadeer')
            ->update(['value' => AdminTheme::DEFAULT, 'updated_at' => now()]);

        $this->syncOptions(['ghadeer']);
    }

    private function syncOptions(array $without = [])
    {
        $row = DB::table('settings')->where('key', 'admin.theme')->first();
        if (! $row) {
            return;
        }

        $field = json_decode($row->field, true) ?: [];
        $field['options'] = array_diff_key(AdminTheme::ALL, array_flip($without));

        DB::table('settings')->where('key', 'admin.theme')
            ->update(['field' => json_encode($field, JSON_UNESCAPED_UNICODE)]);
    }
}
