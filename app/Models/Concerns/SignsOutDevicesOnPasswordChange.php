<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Portal sessions last until logout (remember-me cookie). A new password (e.g. an admin reset
 * after an in-person ID check) gives a new remember token, so every device has to sign in again.
 */
trait SignsOutDevicesOnPasswordChange
{
    protected static function bootSignsOutDevicesOnPasswordChange()
    {
        static::updating(function ($model) {
            if ($model->isDirty('password')) {
                $model->setRememberToken(Str::random(60));
            }
        });
    }
}
