<?php

namespace App\Http\Resources\V1;

use App\Services\Portal\TeacherPhotos;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The signed-in teacher (GET /api/v1/me), PLAN.md §8.
 */
class TeacherMeResource extends JsonResource
{
    public function toArray($request)
    {
        $name = trim((string) $this->name);

        return [
            'role' => 'teacher',
            'id' => $this->id,
            'name' => $name,
            'first_name' => $name === '' ? '' : explode(' ', $name)[0],
            'username' => $this->username,
            'email' => $this->email,
            'photo_url' => TeacherPhotos::url($this->resource),
        ];
    }
}
