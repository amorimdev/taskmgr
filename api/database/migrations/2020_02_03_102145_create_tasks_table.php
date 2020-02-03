<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('project_id');
            $table->string('description');
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tasks');
    }
}
