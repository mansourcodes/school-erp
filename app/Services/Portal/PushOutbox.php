<?php

namespace App\Services\Portal;

use App\Models\Attends;
use App\Models\Message;
use App\Models\PushDelivery;
use App\Models\Student;
use App\Models\StudentNotificationSetting;
use App\Models\User;
use App\Notifications\AttendanceRecordedPush;
use App\Notifications\MessageReceivedPush;
use App\Services\Attendance\ClassroomAttendance;
use App\Services\Attendance\StudentSessions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * Push outbox (PLAN.md §5.12). A save records one row per student who can be notified (a
 * subscribed device, the type switched on); sending is a separate step:
 * - teacher saves: the teacher's app sends the rows one by one, so the save answers at once;
 * - admin saves (no SPA): the server sends right away, as before.
 * Each row is claimed atomically, so two tabs never send the same push twice.
 */
class PushOutbox
{
    /** Rows not sent within this time are dropped: a late "you were marked present" is noise. */
    const TTL_HOURS = 24;

    const MAX_ATTEMPTS = 3;

    /** A send that never finished (the request died) is retried after this many minutes. */
    const STUCK_MINUTES = 5;

    public static function enabled()
    {
        return config('webpush.vapid.public_key') && config('webpush.vapid.private_key');
    }

    /** @return int[] ids of the rows created for the first save of $attendId */
    public function queueAttendance($attendId, $userId)
    {
        $attend = Attends::with(array_values(ClassroomAttendance::STATE_RELATIONS))->find($attendId);
        if (! $attend || ! static::enabled()) {
            return [];
        }

        $rows = [];
        foreach (ClassroomAttendance::STATE_RELATIONS as $state => $relation) {
            foreach ($this->reachable($attend->{$relation}->pluck('id')->all(), 'attendance') as $studentId) {
                $rows[] = ['student_id' => $studentId, 'attend_id' => $attend->id, 'state' => StudentSessions::STATE_NAMES[$state]];
            }
        }

        return $this->insert($rows, 'attendance', $userId);
    }

    /** @return int[] ids of the rows created for the recipients of $message */
    public function queueMessage(Message $message, $userId)
    {
        if (! static::enabled()) {
            return [];
        }

        $rows = array_map(function ($studentId) use ($message) {
            return ['student_id' => $studentId, 'message_id' => $message->id];
        }, $this->reachable($message->recipients()->pluck('student_id')->all(), 'messages'));

        return $this->insert($rows, 'messages', $userId);
    }

    /** The teacher's rows still to send (pending, failed with attempts left, or stuck), oldest first. */
    public function pendingFor(User $user)
    {
        $cutoff = now()->subHours(self::TTL_HOURS);
        $mine = PushDelivery::where('user_id', $user->id);

        (clone $mine)->whereIn('status', [PushDelivery::PENDING, PushDelivery::FAILED])
            ->where('created_at', '<', $cutoff)
            ->update(['status' => PushDelivery::SKIPPED]);
        (clone $mine)->where('status', PushDelivery::SENDING)
            ->where('updated_at', '<', now()->subMinutes(self::STUCK_MINUTES))
            ->update(['status' => PushDelivery::FAILED]);

        return (clone $mine)->where('created_at', '>=', $cutoff)
            ->where(function ($q) {
                $q->where('status', PushDelivery::PENDING)
                    ->orWhere(function ($q) {
                        $q->where('status', PushDelivery::FAILED)->where('attempts', '<', self::MAX_ATTEMPTS);
                    });
            })
            ->with('student:id,name')
            ->orderBy('id')
            ->get();
    }

    /** Sends one row now. @return string the row's status afterwards */
    public function send(PushDelivery $delivery)
    {
        $claimed = PushDelivery::whereKey($delivery->id)
            ->where(function ($q) {
                $q->where('status', PushDelivery::PENDING)
                    ->orWhere(function ($q) {
                        $q->where('status', PushDelivery::FAILED)->where('attempts', '<', self::MAX_ATTEMPTS);
                    });
            })
            ->update(['status' => PushDelivery::SENDING, 'attempts' => DB::raw('attempts + 1'), 'updated_at' => now()]);
        if (! $claimed) {
            return $delivery->fresh()->status;
        }

        $delivery->refresh();
        try {
            $notification = $this->notificationFor($delivery);
            if (! $notification || ! $delivery->student) {
                // The session or message is gone (deleted meanwhile): nothing to say.
                $delivery->update(['status' => PushDelivery::SKIPPED]);
            } else {
                Notification::sendNow($delivery->student, $notification);
                $delivery->update(['status' => PushDelivery::SENT, 'sent_at' => now()]);
            }
        } catch (Throwable $e) {
            report($e);
            $delivery->update(['status' => PushDelivery::FAILED]);
        }

        return $delivery->status;
    }

    /** Admin saves: send every row right away, inside the request. */
    public function sendMany(array $ids)
    {
        foreach (PushDelivery::whereIn('id', $ids)->orderBy('id')->get() as $delivery) {
            $this->send($delivery);
        }
    }

    /** Students of $ids with at least one subscribed device and $type switched on. */
    protected function reachable(array $ids, $type)
    {
        if (! $ids) {
            return [];
        }
        $off = StudentNotificationSetting::whereIn('student_id', $ids)->where($type, false)->pluck('student_id')->all();

        return Student::whereIn('id', array_diff($ids, $off))->whereHas('pushSubscriptions')->orderBy('id')->pluck('id')->all();
    }

    protected function insert(array $rows, $type, $userId)
    {
        $ids = [];
        foreach ($rows as $row) {
            $ids[] = PushDelivery::create($row + ['type' => $type, 'user_id' => $userId, 'status' => PushDelivery::PENDING])->id;
        }

        return $ids;
    }

    protected function notificationFor(PushDelivery $delivery)
    {
        if ($delivery->type === 'attendance') {
            $attend = Attends::with('curriculum')->find($delivery->attend_id);

            return $attend ? new AttendanceRecordedPush(
                $delivery->state,
                optional($attend->curriculum)->curriculum_name,
                $attend->date->toDateString(),
                $attend->start_time,
                $attend->id
            ) : null;
        }

        $message = Message::find($delivery->message_id);

        return $message ? new MessageReceivedPush($message->id, $message->body) : null;
    }
}
