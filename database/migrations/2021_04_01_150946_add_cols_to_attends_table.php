<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColsToAttendsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attends', function (Blueprint $table) {
            //
            Schema::dropIfExists('attends_student_attend');
            Schema::dropIfExists('attends_student_absent');
            Schema::dropIfExists('attends_student_absent_w_excuse');
            Schema::dropIfExists('attends_student_late');
            Schema::dropIfExists('attends_student_late_w_excuse');


            $table->foreignId('attends_student_attend');
            $table->foreignId('attends_student_absent');
            $table->foreignId('attends_student_absent_w_excuse');
            $table->foreignId('attends_student_late');
            $table->foreignId('attends_student_late_w_excuse');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attends', function (Blueprint $table) {

            $table->dropColumn('attends_student_attend');
            $table->dropColumn('attends_student_absent');
            $table->dropColumn('attends_student_absent_w_excuse');
            $table->dropColumn('attends_student_late');
            $table->dropColumn('attends_student_late_w_excuse');
        });


        Schema::create('attends_student_attend', function (Blueprint $table) {
            $table->foreignId('attends_id');
            $table->foreignId('student_id');
        });
        Schema::create('attends_student_absent', function (Blueprint $table) {
            $table->foreignId('attends_id');
            $table->foreignId('student_id');
        });
        Schema::create('attends_student_absent_w_excuse', function (Blueprint $table) {
            $table->foreignId('attends_id');
            $table->foreignId('student_id');
        });
        Schema::create('attends_student_late', function (Blueprint $table) {
            $table->foreignId('attends_id');
            $table->foreignId('student_id');
        });
        Schema::create('attends_student_late_w_excuse', function (Blueprint $table) {
            $table->foreignId('attends_id');
            $table->foreignId('student_id');
        });
    }
}
