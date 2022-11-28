<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSharedAccomplishmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shared_accomplishments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->onUpdate('cascade')->onDelete('cascade');
            $table->string('format', 1);
            $table->foreignId('user_id')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('college_id')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('department_id')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::dropIfExists('shared_accomplishments');
    }
}
