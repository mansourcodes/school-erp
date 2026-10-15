<?php

/*
|--------------------------------------------------------------------------
| Backpack\Settings Routes (app override)
|--------------------------------------------------------------------------
|
| Overrides the package route file so the settings CRUD uses
| App\Http\Controllers\Admin\SettingCrudController (key column + category filter).
|
*/

use Illuminate\Support\Facades\Route;

Route::group([
    'namespace'  => 'App\Http\Controllers\Admin',
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => ['web', backpack_middleware()],
], function () {
    Route::crud('setting', 'SettingCrudController');
});
