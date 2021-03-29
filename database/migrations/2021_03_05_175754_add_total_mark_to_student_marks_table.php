<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalMarkToStudentMarksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('student_marks', function (Blueprint $table) {
            //
            $table->float('total_mark', 5, 2)->nullable();
            $table->string('final_grade', 200)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_marks', function (Blueprint $table) {
            //
            $table->dropColumn('total_mark');
            $table->dropColumn('final_grade');
        });
    }
}
