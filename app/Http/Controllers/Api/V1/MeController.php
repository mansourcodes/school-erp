<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\MeResource;
use App\Http\Resources\V1\ProfileResource;
use App\Http\Resources\V1\TeacherMeResource;
use App\Models\Student;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function show(Request $request)
    {
        return self::resourceFor($request->user());
    }

    /** `role` tells the SPA which shell and navigation to render (PLAN.md §8). */
    public static function resourceFor($user)
    {
        return $user instanceof Student ? new MeResource($user) : new TeacherMeResource($user);
    }

    public function profile(Request $request)
    {
        return new ProfileResource($request->user());
    }
}
