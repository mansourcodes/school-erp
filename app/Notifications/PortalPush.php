<?php

namespace App\Notifications;

use App\Models\StudentNotificationSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

/**
 * Base of the portal's Web Push notifications to students (PLAN.md §4 "Push notifications"):
 * queued, Arabic / RTL, arch icon, a `tag` so duplicates collapse, and a URL the service worker
 * opens on tap. Skipped when VAPID is not configured or the student turned the type off.
 */
abstract class PortalPush extends Notification implements ShouldQueue
{
    use Queueable;

    /** attendance | messages (StudentNotificationSetting columns). */
    abstract protected function type();

    abstract protected function title();

    abstract protected function body();

    abstract protected function tag();

    /** Path inside the SPA, e.g. /attendance?date=2026-09-25. */
    abstract protected function path();

    public function via($notifiable)
    {
        if (! config('webpush.vapid.public_key') || ! config('webpush.vapid.private_key')) {
            return [];
        }
        if (! StudentNotificationSetting::for($notifiable)->{$this->type()}) {
            return [];
        }

        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage())
            ->title($this->title())
            ->body($this->body())
            ->icon('/beta/pwa-192x192.png')
            ->badge('/beta/pwa-64x64.png')
            ->dir('rtl')
            ->lang('ar')
            ->tag($this->tag())
            ->data(['url' => '/beta' . $this->path()]);
    }
}
