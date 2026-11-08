<?php

namespace App\Support;

use App\Models\Attends;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\Student;
use App\Models\User;
use App\Services\Attendance\ClassroomAttendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Figures and charts for the Superadmin part of the admin dashboard; "active" means the course's `is_active`.
 * Both charts return the shape inc/dashboard_donut draws: total, centre value, slices with a colour slot.
 * The dashboard reads them through cached(): computed once, kept CACHE_DAYS, cleared by the refresh button.
 */
class AdminDashboard
{
    public const CACHE_KEY = 'admin-dashboard:figures';

    public const CACHE_DAYS = 4;

    /**
     * Attendance status => [pivot table, colour slot, code], in the portal's display order
     * (frontend entities/attendance/config/states.ts: same colours, codes and order; excused = hatched).
     */
    public const ATTENDANCE = [
        'student_attended' => ['attends_student_attend', 'present', '✓'],
        'student_late' => ['attends_student_late', 'late', 'ت'],
        'student_late_w_excuse' => ['attends_student_late_w_excuse', 'late-excused', 'تع'],
        'student_absent_w_excuse' => ['attends_student_absent_w_excuse', 'absent-excused', 'غع'],
        'student_absent' => ['attends_student_absent', 'absent', '✗'],
    ];

    /** Statuses that count as attended in the rate (late students did come). */
    protected const ATTENDED = ['student_attended', 'student_late', 'student_late_w_excuse'];

    /**
     * Every dashboard figure, cached as plain arrays (no models) with the time they were computed.
     *
     * @return array{stats: array, attendance: array, classrooms: array, teachers: array, computed_at: string}
     */
    public static function cached()
    {
        return Cache::remember(self::CACHE_KEY, now()->addDays(self::CACHE_DAYS), function () {
            return [
                'stats' => self::stats(),
                'attendance' => self::attendance(),
                'classrooms' => self::classrooms(),
                'teachers' => self::teachers(),
                'computed_at' => now()->toIso8601String(),
            ];
        });
    }

    public static function forget()
    {
        Cache::forget(self::CACHE_KEY);
    }

    /** @return array{active_courses: int, active_classrooms: int, active_students: int, students: int} */
    public static function stats()
    {
        // Student::classRooms() joins `courses` itself, so the enrolment is read from the pivot table.
        $enrolled = DB::table('class_room_student')->select('student_id')
            ->whereIn('class_room_id', self::activeClassrooms()->select('id'));

        return [
            'active_courses' => Course::where('is_active', true)->count(),
            'active_classrooms' => self::activeClassrooms()->count(),
            'active_students' => Student::whereIn('id', $enrolled)->count(),
            'students' => Student::count(),
        ];
    }

    /**
     * Student-sessions of the active courses: recorded ones per status, plus "unrecorded" = each expected
     * session up to today (see classrooms()) × its classroom's current students, minus the students that
     * session's record already holds. The centre is the attendance rate over recorded student-sessions only
     * (unrecorded never counts in a rate, as in the teacher portal: StudentSessions).
     */
    public static function attendance()
    {
        $classrooms = self::activeClassrooms()->with('course')->get();
        $activeAttends = Attends::whereIn('class_room_id', $classrooms->pluck('id'))->select('id');

        $counts = [];
        $covered = []; // attends_id => [student_id => true], any status
        foreach (self::ATTENDANCE as $key => [$table]) {
            $rows = DB::table($table)->whereIn('attends_id', clone $activeAttends)->get(['attends_id', 'student_id']);
            $counts[$key] = $rows->count();
            foreach ($rows as $row) {
                $covered[$row->attends_id][$row->student_id] = true;
            }
        }
        $recordedTotal = array_sum($counts);

        $students = DB::table('class_room_student')->whereIn('class_room_id', $classrooms->pluck('id'))
            ->get(['class_room_id', 'student_id'])->groupBy('class_room_id')
            ->map(function ($rows) {
                return array_fill_keys($rows->pluck('student_id')->all(), true);
            });
        $records = self::recordIndex($classrooms);

        $unrecorded = 0;
        foreach (self::expectedSessions($classrooms) as [$classroom, $key]) {
            $enrolled = $students->get($classroom->id, []);
            $held = [];
            foreach ($records[$key] ?? [] as $attendId) {
                $held += array_intersect_key($covered[$attendId] ?? [], $enrolled);
            }
            $unrecorded += count($enrolled) - count($held);
        }
        $counts['unrecorded'] = $unrecorded;

        $chart = self::chart($counts, function ($key) {
            return $key === 'unrecorded' ? __('base.dashboard.attendance_unrecorded') : __('attend.'.$key);
        });
        foreach ($chart['slices'] as $i => $slice) {
            [, $chart['slices'][$i]['slot'], $chart['slices'][$i]['code']] = self::ATTENDANCE[$slice['key']] ?? [null, 'muted', '—'];
        }
        $attended = array_sum(array_intersect_key($counts, array_flip(self::ATTENDED)));
        $chart['centre'] = $chart['total'] ? ($recordedTotal ? round($attended * 100 / $recordedTotal).'%' : '—') : null;

        return $chart;
    }

    /**
     * Expected sessions (each active classroom's timetable periods × their dates, up to today) with an
     * attendance record against those without; the centre is "recorded / total".
     * Dates and matching follow the teacher portal (ClassroomAttendance::slots, TeacherSessions::match):
     * same classroom, subject and date, and the same start hour unless the period has no time.
     */
    public static function classrooms()
    {
        $classrooms = self::activeClassrooms()->with('course')->get();
        $records = self::recordIndex($classrooms);

        $total = $recorded = 0;
        foreach (self::expectedSessions($classrooms) as [, $key]) {
            $total++;
            if (isset($records[$key])) {
                $recorded++;
            }
        }

        $chart = self::chart(['recorded' => $recorded, 'not_recorded' => $total - $recorded], function ($key) {
            return __('base.dashboard.classrooms_'.$key);
        });
        $chart['centre'] = $total ? $recorded.' / '.$total : null;
        $chart['slices'][1]['slot'] = 'muted'; // "not recorded" is an absence, so it stays grey

        return $chart;
    }

    /**
     * Teachers ranked by the attendance sessions of the active classrooms they teach (`class_room_teacher`).
     * A session does not store who recorded it, so a classroom's sessions count for each of its teachers.
     *
     * @return array{most: array, least: array} each a list of {id, name, avatar, sessions, classrooms}
     */
    public static function teachers($limit = 3)
    {
        $counts = DB::table('class_room_teacher')
            ->join('class_rooms', 'class_rooms.id', '=', 'class_room_teacher.class_room_id')
            ->join('courses', 'courses.id', '=', 'class_rooms.course_id')
            ->leftJoin('attends', 'attends.class_room_id', '=', 'class_rooms.id')
            ->where('courses.is_active', true)
            ->whereNull('courses.deleted_at')
            ->whereNull('class_rooms.deleted_at')
            ->groupBy('class_room_teacher.user_id')
            ->selectRaw('class_room_teacher.user_id, count(attends.id) as sessions, count(distinct class_rooms.id) as classrooms')
            ->get()->keyBy('user_id');

        $ranked = User::role('Teacher')->whereIn('id', $counts->keys())->get()
            ->map(function ($user) use ($counts) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar' => backpack_avatar_url($user),
                    'sessions' => (int) $counts[$user->id]->sessions,
                    'classrooms' => (int) $counts[$user->id]->classrooms,
                ];
            });

        // Ties keep a stable order by name.
        $byName = function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        };

        $most = $ranked->sort(function ($a, $b) use ($byName) {
            return ($b['sessions'] <=> $a['sessions']) ?: $byName($a, $b);
        })->take($limit);

        // With few teachers the two lists would repeat names, so "least" only takes the ones not in "most".
        $least = $ranked->diffKeys($most)->sort(function ($a, $b) use ($byName) {
            return ($a['sessions'] <=> $b['sessions']) ?: $byName($a, $b);
        })->take($limit);

        return ['most' => $most->values()->all(), 'least' => $least->values()->all()];
    }

    protected static function activeClassrooms()
    {
        return ClassRoom::whereHas('course', function ($query) {
            $query->where('is_active', true);
        });
    }

    /**
     * Each classroom's expected sessions up to today: timetable periods × their dates (ClassroomAttendance::slots).
     * The key matches recordIndex(): "classroom|curriculum|date", plus "|HH" when the period has a start time.
     *
     * @return \Generator<array{0: ClassRoom, 1: string}>
     */
    protected static function expectedSessions($classrooms)
    {
        $today = now()->toDateString();
        foreach ($classrooms as $classroom) {
            foreach ((new ClassroomAttendance($classroom, []))->slots() as $slot) {
                $hour = $slot['start'] ? '|'.$slot['start']->format('H') : '';
                foreach ($slot['dates'] as $date) {
                    if ($date <= $today) {
                        yield [$classroom, $classroom->id.'|'.$slot['curriculum_id'].'|'.$date.$hour];
                    }
                }
            }
        }
    }

    /**
     * The classrooms' Attends ids by session key, under both the day key and the day + start-hour key,
     * so a timed period matches its hour and a period without a time matches any record that day
     * (the TeacherSessions::match rule).
     *
     * @return array<string, int[]>
     */
    protected static function recordIndex($classrooms)
    {
        $index = [];
        Attends::whereIn('class_room_id', $classrooms->pluck('id'))
            ->get(['id', 'class_room_id', 'curriculum_id', 'date', 'start_time'])
            ->each(function (Attends $attend) use (&$index) {
                $day = $attend->class_room_id.'|'.(int) $attend->curriculum_id.'|'.$attend->date->format('Y-m-d');
                $index[$day][] = $attend->id;
                $index[$day.'|'.Carbon::parse($attend->start_time)->format('H')][] = $attend->id;
            });

        return $index;
    }

    /**
     * @param  array<string, int>  $counts  in colour-slot order
     * @return array{total: int, slices: array<int, array{key: string, label: string, count: int, percent: float, slot: int|string}>}
     */
    protected static function chart(array $counts, callable $label)
    {
        $total = array_sum($counts);
        $slices = [];
        $slot = 1;
        foreach ($counts as $key => $count) {
            $slices[] = [
                'key' => $key,
                'label' => $label($key),
                'count' => $count,
                'percent' => $total ? round($count * 100 / $total, 1) : 0.0,
                'slot' => $slot++,
            ];
        }

        return ['total' => $total, 'slices' => $slices];
    }
}
