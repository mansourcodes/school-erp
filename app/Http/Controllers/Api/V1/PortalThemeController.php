<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\PortalBranding;
use App\Support\PortalMode;
use App\Support\PortalTheme;

class PortalThemeController extends Controller
{
    /**
     * Public: the login screen and the maintenance / closed screens are themed too.
     * Also the panel's texts (PortalBranding: name, short_name, centre, description) and `base`, the
     * panel's page root (/beta/ or / once live), so an app opened at the other one moves over.
     */
    public function show()
    {
        return response()->json(['data' => [
            'theme' => PortalTheme::current(),
            'base' => PortalMode::appPath('/'),
        ] + PortalBranding::all()]);
    }
}
