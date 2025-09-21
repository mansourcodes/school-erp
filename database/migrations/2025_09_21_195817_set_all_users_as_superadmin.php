<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Spatie\Permission\Models\Role;

class SetAllUsersAsSuperadmin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Ensure the Superadmin role exists
        $role = Role::firstOrCreate(['name' => 'Superadmin']);

        // Assign Superadmin role to all current users
        User::all()->each(function ($user) use ($role) {
            $user->assignRole($role);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $role = Role::where('name', 'Superadmin')->first();

        if ($role) {
            User::all()->each(function ($user) use ($role) {
                $user->removeRole($role);
            });
        }
    }
}
