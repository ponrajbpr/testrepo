<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('club_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->string('acadmey_name')->nullable();
            $table->string('short_code')->nullable();
            $table->string('registered_certificate')->nullable();
            $table->string('pan_no')->nullable();
            $table->string('practice_place')->nullable();
            $table->string('number_of_players')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('caddress1')->nullable();
            $table->string('caddress2')->nullable();
            $table->string('ccity')->nullable();
            $table->string('cstate')->nullable();
            $table->string('cdistrict')->nullable();
            $table->string('cpincode')->nullable();
            $table->string('club_director')->nullable();
            $table->string('club_mobile')->nullable();
            $table->string('club_email')->nullable();
            $table->string('club_aadhr')->nullable();
            $table->string('photo')->nullable();
            $table->string('aadhar')->nullable();
            $table->string('academey_photo')->nullable();
            $table->string('academey_logo')->nullable();
            $table->string('status')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('club_details');
    }
};
