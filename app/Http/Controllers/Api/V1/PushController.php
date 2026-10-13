<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\StudentNotificationSetting;
use Illuminate\Http\Request;

/**
 * Web Push for students (PLAN.md §5.12, §7.2): this device's subscription and the per-type
 * settings (server-side, so they apply to every device).
 */
class PushController extends Controller
{
    /** GET /push/config: the VAPID public key the browser subscribes with. */
    public function config()
    {
        $key = config('webpush.vapid.public_key');

        return response()->json(['data' => ['enabled' => (bool) $key, 'public_key' => $key ?: null]]);
    }

    /** POST /push/subscriptions: store / refresh this device's subscription. */
    public function subscribe(Request $request)
    {
        $data = $request->validate([
            'endpoint' => ['required', 'url', 'starts_with:https://', 'max:500'],
            'keys.p256dh' => ['required', 'string', 'max:255'],
            'keys.auth' => ['required', 'string', 'max:255'],
            'content_encoding' => ['nullable', 'in:aesgcm,aes128gcm'],
        ]);

        $request->user()->updatePushSubscription(
            $data['endpoint'],
            $data['keys']['p256dh'],
            $data['keys']['auth'],
            $data['content_encoding'] ?? 'aes128gcm'
        );

        return response()->noContent();
    }

    /** DELETE /push/subscriptions: on logout, so a shared phone stops receiving this student's pushes. */
    public function unsubscribe(Request $request)
    {
        $data = $request->validate(['endpoint' => ['required', 'string', 'max:500']]);
        $request->user()->deletePushSubscription($data['endpoint']);

        return response()->noContent();
    }

    /** GET /student/notification-settings */
    public function settings(Request $request)
    {
        return response()->json(['data' => $this->settingsArray(StudentNotificationSetting::for($request->user()))]);
    }

    /** PATCH /student/notification-settings {attendance?, messages?} */
    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'attendance' => ['sometimes', 'boolean'],
            'messages' => ['sometimes', 'boolean'],
        ]);
        $settings = StudentNotificationSetting::for($request->user());
        $settings->fill($data)->save();

        return response()->json(['data' => $this->settingsArray($settings)]);
    }

    protected function settingsArray(StudentNotificationSetting $settings)
    {
        return ['attendance' => (bool) $settings->attendance, 'messages' => (bool) $settings->messages];
    }
}
