<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PushDelivery;
use App\Services\Portal\PushOutbox;
use Illuminate\Http\Request;

/**
 * The teacher's push outbox (PLAN.md §5.12): after a save or a message the app sends the pushes
 * one request at a time, so the save answers at once and the teacher sees the progress.
 */
class PushDeliveryController extends Controller
{
    /** GET /teacher/push-deliveries: my rows still to send, oldest first. */
    public function index(Request $request, PushOutbox $outbox)
    {
        return response()->json(['data' => $outbox->pendingFor($request->user())->map(function (PushDelivery $delivery) {
            return [
                'id' => $delivery->id,
                'type' => $delivery->type,
                'student_name' => optional($delivery->student)->name,
            ];
        })->values()]);
    }

    /** POST /teacher/push-deliveries/{id}/send: one student's push; my rows only. */
    public function send(Request $request, PushOutbox $outbox, $id)
    {
        $delivery = PushDelivery::where('user_id', $request->user()->id)->find($id);
        abort_unless($delivery, 404);

        return response()->json(['data' => ['id' => $delivery->id, 'status' => $outbox->send($delivery)]]);
    }
}
