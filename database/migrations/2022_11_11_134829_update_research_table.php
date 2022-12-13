<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateResearchTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // $this->down();
        // Schema::create('researchers', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('research_id')->onUpdate('cascade')->onDelete('cascade');
        //     $table->foreignId('department_id')->onUpdate('cascade')->onDelete('cascade');
        //     $table->foreignId('college_id')->onUpdate('cascade')->onDelete('cascade');
        //     $table->foreignId('user_id')->onUpdate('cascade')->onDelete('cascade');
        //     $table->foreignId('nature_of_involvement')->nullable();
        //     $table->integer('is_registrant')->default(0);
        //     $table->timestamps();
        //     $table->softDeletes();
        // });
        
        // Schema::table('research', function (Blueprint $table) {
        //     $table->integer('has_new_commitment')->default(0)->after('description');
        // });

        // Schema::create('research_tags', function (Blueprint $table) {
        //     //extensionists that were tagged waiting for confirmation
        //     $table->id();
        //     $table->foreignId('research_id')->onUpdate('cascade')->onDelete('cascade');
        //     $table->foreignId('sender_id');
        //     $table->foreignId('user_id')->onUpdate('cascade')->onDelete('cascade');
        //     $table->boolean('status')->nullable();
        //     $table->timestamps();
        //     $table->softDeletes();
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('research', function (Blueprint $table) {
            $table->dropColumn('research_code');
            $table->dropColumn('nature_of_involvement');
            $table->dropColumn('college_id');
            $table->dropColumn('department_id');
            $table->dropColumn('user_id');
            $table->dropColumn('is_active_member');
            $table->dropColumn('deleted_at');
            $table->dropColumn('report_quarter');
            $table->dropColumn('report_year');
        });
        Schema::table('research_completes', function (Blueprint $table) {
            $table->dropColumn('research_code');
            $table->dropColumn('deleted_at');
            $table->dropColumn('report_quarter');
            $table->dropColumn('report_year');
        });
        Schema::table('research_presentations', function (Blueprint $table) {
            $table->dropColumn('research_code');
            $table->dropColumn('deleted_at');
            $table->dropColumn('report_quarter');
            $table->dropColumn('report_year');
        });
        Schema::table('research_publications', function (Blueprint $table) {
            $table->dropColumn('research_code');
            $table->dropColumn('deleted_at');
            $table->dropColumn('report_quarter');
            $table->dropColumn('report_year');
        });
        Schema::table('research_citations', function (Blueprint $table) {
            $table->dropColumn('report_quarter');
            $table->dropColumn('report_year');
        });
        Schema::table('research_utilizations', function (Blueprint $table) {
            $table->dropColumn('report_quarter');
            $table->dropColumn('report_year');
        });
        Schema::table('research_copyrights', function (Blueprint $table) {
            $table->dropColumn('research_code');
            $table->dropColumn('deleted_at');
            $table->dropColumn('report_quarter');
            $table->dropColumn('report_year');
        });
        Schema::table('research_documents', function (Blueprint $table) {
            $table->dropColumn('research_code');
        });
    }
}
