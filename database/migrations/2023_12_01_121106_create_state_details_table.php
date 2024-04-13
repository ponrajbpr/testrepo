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
        Schema::create('state_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->integer('association_id');
            $table->string('association_name');
            $table->string('office_address1');
            $table->string('office_address2');
            $table->string('office_pincode');
            $table->string('office_district');
            $table->string('office_state');
            $table->string('office_city');
            $table->string('office_email');
            $table->string('office_phone');
            $table->string('website');
            $table->string('president_name');
            $table->string('president_mobile');
            $table->string('president_gender');
            $table->string('president_dob');
            $table->string('president_email');
            $table->string('president_address');
            $table->string('president_district');
            $table->string('president_state');
            $table->string('president_pincode');
            $table->string('secretary_name');
            $table->string('secretary_mobile');
            $table->string('secretary_gender');
            $table->string('secretary_dob');
            $table->string('secretary_email');
            $table->string('secretary_address');
            $table->string('secretary_district');
            $table->string('secretary_state');
            $table->string('secretary_pincode');
            $table->string('constitution_document');
            $table->string('last_election_date');
            $table->string('last_election_result');
            $table->string('registration_certificate');
            $table->string('secretary_photo');
            $table->string('association_logo');
            $table->string('bank_name');
            $table->string('branch_name');
            $table->string('account_number');
            $table->string('ifsc_code');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('state_details');
    }
};
