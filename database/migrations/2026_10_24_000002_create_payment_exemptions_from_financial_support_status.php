<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * One exemption ticket per (student, inactive course) for students whose `financial_support_status` is a real
 * support reason (NONE and UNKNOWN mean "no support"). Students who already hold a ticket in that course are
 * skipped, so the migration is safe to re-run. Tickets are marked by NOTES so down() removes only these.
 */
class CreatePaymentExemptionsFromFinancialSupportStatus extends Migration
{
    const REASONS = ['FINANCIAL_ISSUE', 'PARENTS_ISSUE', 'TEACHER_SONS'];

    const NOTES = 'migrated from financial_support_status';

    public function up()
    {
        $rows = DB::table('class_room_student')
            ->join('class_rooms', 'class_rooms.id', '=', 'class_room_student.class_room_id')
            ->join('courses', 'courses.id', '=', 'class_rooms.course_id')
            ->join('students', 'students.id', '=', 'class_room_student.student_id')
            ->where('courses.is_active', 0)
            ->whereNull('courses.deleted_at')
            ->whereNull('class_rooms.deleted_at')
            ->whereNull('students.deleted_at')
            ->whereIn('students.financial_support_status', self::REASONS)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('payment_exemptions')
                    ->whereColumn('payment_exemptions.student_id', 'students.id')
                    ->whereColumn('payment_exemptions.course_id', 'courses.id')
                    ->whereNull('payment_exemptions.deleted_at');
            })
            ->select('students.id as student_id', 'courses.id as course_id', 'students.financial_support_status as reason')
            ->distinct()
            ->get();

        $now = now();
        foreach ($rows->chunk(500) as $chunk) {
            DB::table('payment_exemptions')->insert($chunk->map(function ($row) use ($now) {
                return [
                    'student_id' => $row->student_id,
                    'course_id' => $row->course_id,
                    'reason' => $row->reason,
                    'notes' => self::NOTES,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })->all());
        }

        Log::info('payment_exemptions migrated from financial_support_status', ['tickets' => $rows->count()]);
    }

    public function down()
    {
        DB::table('payment_exemptions')->where('notes', self::NOTES)->whereNull('created_by')->delete();
    }
}
