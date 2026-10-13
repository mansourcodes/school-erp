<?php

namespace App\Http\Resources\V1;

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
            // Set by teachers/admin from Phase 3 (PLAN.md §5.8).
            'photo_url' => null,
        ];
    }
}
