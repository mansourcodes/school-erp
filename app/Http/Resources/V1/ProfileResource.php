<?php

namespace App\Http\Resources\V1;

use App\Services\Portal\StudentPhotos;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The student's own read-only profile (PLAN.md §3.6). Whitelist: never financial_*,
 * health_*, hawza_history_details, student_notes, relationship_state or family_*.
 */
class ProfileResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'name' => $this->name,
            // Stored as an integer, so a leading zero (born 2000–2009) is lost; CPRs have 9 digits.
            'cpr' => $this->cpr === null ? null : str_pad((string) $this->cpr, 9, '0', STR_PAD_LEFT),
            'dob' => optional($this->dob)->toDateString(),
            // The accessor returns 'error' or 0 when it cannot tell.
            'age' => is_int($this->age) && $this->age > 0 ? $this->age : null,
            'degree' => $this->degree,
            'mobile' => $this->mobile ? (string) $this->mobile : null,
            'mobile2' => $this->mobile2 ? (string) $this->mobile2 : null,
            'email' => $this->email,
            'address' => $this->address,
            'student_id' => $this->student_id,
            'registration_at' => optional($this->registration_at ?: $this->created_at)->toDateString(),
            'iqraa_id_account' => $this->iqraa_id_account,
            'photo_url' => StudentPhotos::url($this->resource),
        ];
    }
}
