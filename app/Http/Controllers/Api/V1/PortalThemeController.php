<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\PortalTheme;

class PortalThemeController extends Controller
{
    /** Public: the login screen and the maintenance / closed screens are themed too. */
    public function show()
    {
        return response()->json(['data' => ['theme' => PortalTheme::current()]]);
    }
}
