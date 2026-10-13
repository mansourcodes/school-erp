<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Push outbox: one row per student to notify after a teacher save or message. The teacher's
 * app sends them one by one (POST /teacher/push-deliveries/{id}/send), so the save itself
 * returns at once even with QUEUE_CONNECTION=sync and no worker.
 */
class CreatePushDeliveriesTable extends Migration
{
    public function up()
    {
        Schema::create('push_deliveries', function (Blueprint $table) {
            $table->id();
            // The teacher whose app sends it; null = sent by the server at once (admin saves).
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('student_id');
            $table->string('type', 16); // attendance | messages
            $table->unsignedBigInteger('attend_id')->nullable();
            $table->unsignedBigInteger('message_id')->nullable();
            $table->string('state', 16)->nullable(); // attendance: API state of this student
            $table->string('status', 10)->default('pending'); // pending | sending | sent | failed | skipped
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('attend_id');
            $table->index('message_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('push_deliveries');
    }
}
