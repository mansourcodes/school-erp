<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Users (admins and teachers) sign in with a username instead of their email; the email stays as
 * contact information. Existing users get the part of their email before "@" (lowercased, limited
 * to a-z 0-9 . _ -), with a number appended when two users would share one.
 */
class AddUsernameToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->nullable()->unique()->after('name');
        });

        $taken = [];
        DB::table('users')->orderBy('id')->select(['id', 'email'])->each(function ($user) use (&$taken) {
            $base = preg_replace('/[^a-z0-9._-]/', '', strtolower(strstr((string) $user->email, '@', true) ?: (string) $user->email));
            $base = substr($base, 0, 40);
            if (strlen($base) < 3) {
                $base = 'user' . $user->id;
            }

            $username = $base;
            for ($n = 2; isset($taken[$username]); $n++) {
                $username = $base . $n;
            }
            $taken[$username] = true;

            DB::table('users')->where('id', $user->id)->update(['username' => $username]);
        });

        // Every user now has one; the column cannot be changed without doctrine/dbal, so MySQL only.
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE users MODIFY username VARCHAR(50) NOT NULL');
        }
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }
}
