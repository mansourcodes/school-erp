<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-student push preferences (PLAN.md §5.12): stored server-side so they apply to every
 * device of the student. No row = both on.
 */
class CreateStudentNotificationSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('student_notification_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id')->unique();
            $table->boolean('attendance')->default(true);
            $table->boolean('messages')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_notification_settings');
    }
}
