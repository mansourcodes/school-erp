<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Attends.start_time used to be saved with format 'H:m' (m = month, not minutes),
 * so a 08:55 class recorded in October was stored as 08:10.
 * Restore the real time from the classroom timetable (attend_table) slot that
 * matches the record's curriculum, week day and hour.
 */
class RepairAttendsStartTimeMinutes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $timetables = DB::table('class_rooms')->pluck('attend_table', 'id');

        $repaired = 0;
        $skipped = [];

        foreach (DB::table('attends')->orderBy('id')->cursor() as $attend) {
            $slots = json_decode($timetables[$attend->class_room_id] ?? 'null', true) ?: [];
            $day = Carbon::parse($attend->date)->format('N');
            $hour = Carbon::parse($attend->start_time)->format('H');

            $matches = array_values(array_filter($slots, function ($slot) use ($attend, $day, $hour) {
                // empty start_time would parse as "now", so such slots can't be used
                return isset($slot['curriculum_id'], $slot['day'])
                    && !empty($slot['start_time'])
                    && (int)$slot['curriculum_id'] === (int)$attend->curriculum_id
                    && (string)$slot['day'] === $day
                    && Carbon::parse($slot['start_time'])->format('H') === $hour;
            }));

            if (count($matches) !== 1) {
                $skipped[] = $attend->id;
                continue;
            }

            $start_time = Carbon::parse($matches[0]['start_time'])->format('H:i:s');
            if ($start_time === Carbon::parse($attend->start_time)->format('H:i:s')) {
                continue;
            }

            try {
                DB::table('attends')->where('id', $attend->id)->update(['start_time' => $start_time]);
                $repaired++;
            } catch (\Illuminate\Database\QueryException $e) {
                // unique (date, start_time, class_room_id, curriculum_id) collision
                $skipped[] = $attend->id;
            }
        }

        Log::info('Attends start_time repair', ['repaired' => $repaired, 'skipped_ids' => $skipped]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // the original (wrong) minutes are not recoverable and not worth restoring
    }
}
