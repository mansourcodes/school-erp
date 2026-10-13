<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\MeResource;
use App\Http\Resources\V1\ProfileResource;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function show(Request $request)
    {
        return new MeResource($request->user());
    }

    public function profile(Request $request)
    {
        return new ProfileResource($request->user());
    }
}
