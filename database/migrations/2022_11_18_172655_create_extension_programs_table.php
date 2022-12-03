<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExtensionProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('extension_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level')->nullable();
            $table->foreignId('status')->nullable();
            $table->string('classification')->nullable();
            $table->foreignId('type')->nullable();
            $table->string('title_of_extension_program')->nullable();
            $table->string('title_of_extension_project')->nullable();
            $table->string('title_of_extension_activity')->nullable();
            $table->string('extensionists')->nullable();
            $table->string('funding_agency')->nullable();
            $table->foreignId('currency_amount_of_funding')->nullable();
            $table->decimal('amount_of_funding', 15, 2)->nullable();
            $table->foreignId('type_of_funding')->nullable();
            $table->date('from')->nullable();
            $table->date('to')->nullable();
            $table->integer('no_of_trainees_or_beneficiaries')->nullable();
            $table->decimal('total_no_of_hours', 9, 1)->nullable();
            $table->string('classification_of_trainees_or_beneficiaries')->nullable();
            $table->string('place_or_venue')->nullable();
            $table->string('keywords')->nullable();
            $table->integer('quality_poor')->nullable();
            $table->integer('quality_fair')->nullable();
            $table->integer('quality_satisfactory')->nullable();
            $table->integer('quality_very_satisfactory')->nullable();
            $table->integer('quality_outstanding')->nullable();
            $table->integer('timeliness_poor')->nullable();
            $table->integer('timeliness_fair')->nullable();
            $table->integer('timeliness_satisfactory')->nullable();
            $table->integer('timeliness_very_satisfactory')->nullable();
            $table->integer('timeliness_outstanding')->nullable();
            $table->text('description')->nullable();
            $table->integer('report_quarter');
            $table->integer('report_year');
            $table->timestamps();

        });
        Schema::create('extensionists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extension_program_id')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('department_id')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('college_id')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('user_id')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('nature_of_involvement')->nullable();
            $table->integer('is_registrant')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('extension_tags', function (Blueprint $table) {
            //extensionists that were tagged waiting for confirmation
            $table->id();
            $table->foreignId('extension_program_id')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('sender_id');
            $table->foreignId('user_id')->onUpdate('cascade')->onDelete('cascade');
            $table->boolean('status')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('extension_program_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extension_program_id')->onUpdate('cascade')->onDelete('cascade');
            $table->string('filename');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('extension_programs');
        Schema::dropIfExists('extensionists');
        Schema::dropIfExists('extension_tags');
        Schema::dropIfExists('extension_program_documents');
    }
}
