<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One-way teacher → student messages (PLAN.md §5.11). Soft-deleted (project rule): a teacher's
 * delete hides the message from every inbox at once; `messages:prune` removes old ones (90 days).
 */
class CreateMessagesTables extends Migration
{
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_user_id')->constrained('users');
            $table->unsignedBigInteger('class_room_id');
            $table->text('body');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['sender_user_id', 'created_at']);
            $table->index('class_room_id');
        });

        Schema::create('message_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('messages')->cascadeOnDelete();
            $table->unsignedBigInteger('student_id');
            $table->timestamp('read_at')->nullable();

            $table->unique(['message_id', 'student_id']);
            $table->index(['student_id', 'read_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('message_recipients');
        Schema::dropIfExists('messages');
    }
}
