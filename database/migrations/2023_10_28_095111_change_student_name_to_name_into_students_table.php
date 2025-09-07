<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeStudentNameToNameIntoStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            Schema::table('students', function (Blueprint $table) {
                $table->renameColumn('student_name', 'name');
            });
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        try {
            //code...
            Schema::table('students', function (Blueprint $table) {
                $table->renameColumn('name', 'student_name');
            });
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
