<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Account reports now read exemption tickets: their title / pre / pro settings follow the renamed reports,
 * the unconfirmed students report is gone, and the new paid students report gets its own settings.
 */
class RenameAccountReportSettings extends Migration
{
    const RENAMED = [
        'detecting_helpers_report' => 'exempt_students_report',
        'list_of_assistance_students_who_participated_in_the_payment_report' => 'paid_and_exempt_students_report',
    ];

    const REMOVED = 'list_of_unconfirmed_students_report';

    const ADDED = 'paid_students_report';

    const PARTS = [
        'title' => '{"name":"value","label":"Value","type":"text"}',
        'pre' => '{"name":"value","label":"Value","type":"tinymce"}',
        'pro' => '{"name":"value","label":"Value","type":"tinymce"}',
    ];

    public function up()
    {
        foreach (self::RENAMED as $old => $new) {
            $this->rename($old, $new);
        }

        DB::table('settings')->where('key', 'like', self::REMOVED . '.%')->delete();

        foreach (self::PARTS as $part => $field) {
            $key = self::ADDED . '.' . $part;
            if (!DB::table('settings')->where('key', $key)->exists()) {
                DB::table('settings')->insert([
                    'key' => $key,
                    'name' => $this->name($part, self::ADDED),
                    'description' => '',
                    'value' => '',
                    'field' => $field,
                    'active' => 1,
                ]);
            }
        }

        // Its label changed to "not paid and not exempt".
        foreach (array_keys(self::PARTS) as $part) {
            DB::table('settings')->where('key', 'list_of_non_paying_students_report.' . $part)
                ->update(['name' => $this->name($part, 'list_of_non_paying_students_report')]);
        }
    }

    public function down()
    {
        foreach (self::RENAMED as $old => $new) {
            $this->rename($new, $old);
        }

        DB::table('settings')->where('key', 'like', self::ADDED . '.%')->delete();
    }

    protected function rename($from, $to)
    {
        foreach (array_keys(self::PARTS) as $part) {
            DB::table('settings')->where('key', $from . '.' . $part)
                ->update(['key' => $to . '.' . $part, 'name' => $this->name($part, $to)]);
        }
    }

    /** Same naming as 2022_12_03_112812_add_settings_to_settings_table_6. */
    protected function name($part, $report)
    {
        return trans('reports.' . $part) . ':' . trans('reports.' . $report);
    }
}
