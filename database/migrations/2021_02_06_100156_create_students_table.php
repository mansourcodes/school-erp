<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200)->nullable();
            $table->unsignedBigInteger('cpr')->unique()->nullable();
            $table->string('email', 100)->nullable();
            $table->unsignedBigInteger('mobile')->nullable();
            $table->unsignedBigInteger('mobile2')->nullable();
            $table->date('dob')->nullable();
            $table->string('address', 200)->nullable();
            $table->enum('live_in_state', ["UNKNOWN", "OWN", "RENT"]);
            $table->enum('relationship_state', ["UNKNOWN", "SINGLE", "MARRIED", "DIVORCED"]);
            $table->unsignedInteger('family_members')->default('0')->nullable();
            $table->unsignedInteger('family_depends')->default('0')->nullable();
            $table->string('degree', 200)->nullable();
            $table->boolean('hawza_history')->default('0')->nullable();
            $table->text('hawza_history_details')->nullable();
            $table->boolean('health_history')->default('0')->nullable();
            $table->text('health_history_details')->nullable();
            $table->enum('financial_state', ["UNKNOWN", "POOR", "AVERAGE", "GOOD", "EXCELLENT"]);
            $table->text('financial_details')->nullable();
            $table->text('student_notes')->nullable();
            $table->date('registration_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('students');
    }
}
