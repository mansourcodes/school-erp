<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class AddDefaultRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Insert roles only if they don't already exist
        if (!Role::where('name', 'Superadmin')->exists()) {
            Role::create(['name' => 'Superadmin']);
        }

        if (!Role::where('name', 'Teacher')->exists()) {
            Role::create(['name' => 'Teacher']);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Role::where('name', 'Superadmin')->delete();
        Role::where('name', 'Teacher')->delete();
    }
}
