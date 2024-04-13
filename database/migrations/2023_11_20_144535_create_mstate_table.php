<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('mstate', function (Blueprint $table) {
            $table->id(); // Assuming an auto-incrementing primary key
            $table->integer('countryUID');
            $table->string('stateName');
            $table->string('stateCode');
            $table->boolean('isActive')->default(1);
            $table->string('createdBy')->nullable();
            $table->string('modifiedBy')->nullable();
            // Add other columns as needed

            $table->timestamps(); // Laravel timestamps (created_at and updated_at)
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mstate');
    }
};
