<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\MessageRecipient;
use App\Models\PushDelivery;
use App\Services\Portal\StudentPhotos;
use App\Services\Portal\TeacherPhotos;
use App\Services\Teacher\TeacherScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * One-way messages (PLAN.md §5.11, §7.2, §8): teachers send to selected students of one of their
 * current classrooms and may delete their own; students read their inbox and mark messages read.
 */
class MessageController extends Controller
{
    const PER_PAGE = 20;

    /** GET /teacher/messages: my sent messages (not deleted), newest first, with read progress. */
    public function sent(Request $request)
    {
        $page = Message::where('sender_user_id', $request->user()->id)
            ->with(['classroom', 'recipients.student'])
            ->latest('id')
            ->paginate(self::PER_PAGE);

        return response()->json([
            'data' => collect($page->items())->map(function (Message $message) {
                $recipients = $message->recipients->sortBy(function ($r) {
                    return optional($r->student)->name;
                })->values();

                return [
                    'id' => $message->id,
                    'classroom' => $this->classroomLabel($message),
                    'body' => $message->body,
                    'sent_at' => $message->created_at->toIso8601String(),
                    'recipients_count' => $recipients->count(),
                    'read_count' => $recipients->whereNotNull('read_at')->count(),
                    'recipients' => $recipients->map(function (MessageRecipient $r) {
                        return [
                            'id' => $r->student_id,
                            'name' => optional($r->student)->name,
                            'photo_url' => $r->student ? StudentPhotos::url($r->student) : null,
                            'read_at' => optional($r->read_at)->toIso8601String(),
                        ];
                    }),
                ];
            }),
            'meta' => ['current_page' => $page->currentPage(), 'last_page' => $page->lastPage()],
        ]);
    }

    /**
     * POST /teacher/messages {classroom_id, student_ids[], body}: body 1–500 characters; the classroom
     * must be one of my current ones (else 404) and every student enrolled in it (else 422).
     */
    public function send(Request $request)
    {
        $data = $request->validate([
            'classroom_id' => ['required', 'integer'],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'distinct'],
            'body' => ['required', 'string', 'max:' . Message::MAX_LENGTH],
        ]);
        $body = trim($data['body']);
        if ($body === '') {
            throw ValidationException::withMessages(['body' => [__('portal.messages.empty')]]);
        }

        $classroom = (new TeacherScope($request->user()))->find($data['classroom_id']);
        abort_unless($classroom, 404);

        $enrolled = $classroom->students->pluck('id')->all();
        if (array_diff($data['student_ids'], $enrolled)) {
            throw ValidationException::withMessages(['student_ids' => [__('portal.messages.foreign_students')]]);
        }

        $message = DB::transaction(function () use ($request, $classroom, $body, $data) {
            $message = Message::create([
                'sender_user_id' => $request->user()->id,
                'class_room_id' => $classroom->id,
                'body' => $body,
            ]);
            $message->recipients()->createMany(array_map(function ($id) {
                return ['student_id' => $id];
            }, array_values(array_unique($data['student_ids']))));

            return $message;
        });

        event(new \App\Events\MessageSent($message));

        return response()->json(['data' => [
            'id' => $message->id,
            'recipients_count' => count($data['student_ids']),
            // Pushes the app now sends one by one (push outbox).
            'push_pending' => PushDelivery::where('message_id', $message->id)->where('status', PushDelivery::PENDING)->count(),
        ]], 201);
    }

    /** DELETE /teacher/messages/{id}: my own only; it leaves every inbox at once. */
    public function destroy(Request $request, $id)
    {
        $message = Message::where('sender_user_id', $request->user()->id)->find($id);
        abort_unless($message, 404);
        $message->delete();

        return response()->noContent();
    }

    /** GET /student/messages: my inbox (messages not deleted), newest first. */
    public function inbox(Request $request)
    {
        $page = MessageRecipient::where('student_id', $request->user()->id)
            ->whereHas('message')
            ->with(['message.sender', 'message.classroom'])
            ->orderByDesc('message_id')
            ->paginate(self::PER_PAGE);

        return response()->json([
            'data' => collect($page->items())->map(function (MessageRecipient $r) {
                return [
                    'id' => $r->message_id,
                    'teacher' => optional($r->message->sender)->name,
                    'teacher_photo_url' => $r->message->sender ? TeacherPhotos::url($r->message->sender) : null,
                    'classroom' => $this->classroomLabel($r->message),
                    'body' => $r->message->body,
                    'sent_at' => $r->message->created_at->toIso8601String(),
                    'read_at' => optional($r->read_at)->toIso8601String(),
                ];
            }),
            'meta' => ['current_page' => $page->currentPage(), 'last_page' => $page->lastPage()],
        ]);
    }

    /** POST /student/messages/{id}/read: idempotent; 404 when deleted or not mine. */
    public function read(Request $request, $id)
    {
        $recipient = MessageRecipient::where('student_id', $request->user()->id)
            ->where('message_id', $id)
            ->whereHas('message')
            ->first();
        abort_unless($recipient, 404);

        if (! $recipient->read_at) {
            $recipient->forceFill(['read_at' => now()])->save();
        }

        return response()->json(['data' => ['read_at' => $recipient->read_at->toIso8601String()]]);
    }

    /** Unread count for the top-bar badge (GET /me). */
    public static function unreadCount($studentId)
    {
        return MessageRecipient::where('student_id', $studentId)->whereNull('read_at')->whereHas('message')->count();
    }

    protected function classroomLabel(Message $message)
    {
        $classroom = $message->classroom;

        return $classroom ? ['id' => $classroom->id, 'number' => $classroom->class_room_number, 'name' => $classroom->class_room_name] : null;
    }
}
