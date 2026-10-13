<?php

namespace App\Notifications;

use Carbon\Carbon;

/**
 * "تم رصد حضورك: حاضر ✓" (PLAN.md §4): sent once, on the first save of a session; edits never
 * notify. Every student gets their own status (Q17). No CPR or marks in the payload.
 */
class AttendanceRecordedPush extends PortalPush
{
    /** @var string API state: present | late | late_excused | absent | absent_excused */
    public $state;

    public $curriculum;

    public $date;

    public $start;

    public $attendId;

    public function __construct($state, $curriculum, $date, $start, $attendId)
    {
        $this->state = $state;
        $this->curriculum = $curriculum;
        $this->date = $date;
        $this->start = $start;
        $this->attendId = $attendId;
    }

    protected function type()
    {
        return 'attendance';
    }

    protected function title()
    {
        return __('portal.push.attendance.' . $this->state);
    }

    protected function body()
    {
        $date = Carbon::parse($this->date)->locale('ar');
        $time = $this->start ? Carbon::parse($this->start)->format('g:i') . ' ' . (Carbon::parse($this->start)->format('A') === 'PM' ? 'م' : 'ص') : null;
        $line = implode(' · ', array_filter([$this->curriculum, $date->dayName . ' ' . $date->day . ' ' . $date->monthName, $time]));

        // Absent: tell the student what to do (PLAN.md §4 table).
        return $this->state === 'absent' ? $line . "\n" . __('portal.push.attendance.absent_hint') : $line;
    }

    protected function tag()
    {
        return 'attendance-' . $this->attendId;
    }

    protected function path()
    {
        return '/attendance?date=' . $this->date;
    }
}
