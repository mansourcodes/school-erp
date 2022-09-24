<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExamToolsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('exam_tools', function (Blueprint $table) {
            $table->id();
            $table->string('subject', 200)->nullable();
            $table->foreignId('course_id')->constrained();
            $table->string('zip_file_path', 200)->nullable();
            $table->string('zip_file_size', 200)->nullable();
            $table->longText('meta')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exam_tools');
    }
}
