<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassRoomTeacherTable extends Migration
{
    public function up()
    {
        Schema::create('class_room_teacher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_room_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // teacher (user)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('class_room_teacher');
    }
}
