<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Student photo (PLAN.md §5.8): set by teachers or the admin; the file lives on the
 * public disk, only its path is stored (invariant 6: no binaries in the database).
 */
class AddPhotoPathToStudentsTable extends Migration
{
    public function up()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('iqraa_id_account');
        });
    }

    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('photo_path');
        });
    }
}
