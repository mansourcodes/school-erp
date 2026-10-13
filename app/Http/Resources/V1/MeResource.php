<?php

namespace App\Http\Resources\V1;

use App\Http\Controllers\Api\V1\MessageController;
use App\Services\Portal\StudentPhotos;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The signed-in student (GET /api/v1/me). Whitelisted fields only.
 */
class MeResource extends JsonResource
{
    public function toArray($request)
    {
        $name = trim((string) $this->name);

        return [
            'role' => 'student',
            'id' => $this->id,
            'name' => $name,
            'first_name' => $name === '' ? '' : explode(' ', $name)[0],
            'student_id' => $this->student_id,
            // Set by teachers or the admin (PLAN.md §5.8, Q4).
            'photo_url' => StudentPhotos::url($this->resource),
            // Top-bar envelope badge (PLAN.md §7.2), refetched on focus and after a message push.
            'unread_messages' => MessageController::unreadCount($this->id),
        ];
    }
}
