<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Users sign in with their username, so the email is optional contact information (still unique when set;
 * a MySQL unique index allows many NULLs). The column cannot be changed without doctrine/dbal, so MySQL only.
 */
class MakeUsersEmailNullable extends Migration
{
    public function up()
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE users MODIFY email VARCHAR(255) NULL');
        }
    }

    public function down()
    {
        if (DB::getDriverName() === 'mysql') {
            // Users created without an email get a placeholder, so the column can be required again.
            DB::table('users')->whereNull('email')->update(['email' => DB::raw("CONCAT(username, '@no-email.invalid')")]);
            DB::statement('ALTER TABLE users MODIFY email VARCHAR(255) NOT NULL');
        }
    }
}
