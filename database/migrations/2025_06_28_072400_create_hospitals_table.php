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
        Schema::create('hospitals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('address');
            $table->string('hospital_type');
            $table->string('registration_number');
            $table->string('logo')->nullable();
            $table->string('license')->nullable();
            $table->boolean('request_on_site_setup')->nullable();
            $table->boolean('accept_terms');
            $table->string('street_address');
            $table->string('city');
            $table->string('state');
            $table->string('google_maps_location')->nullable();
            $table->integer('number_of_beds')->nullable();
            $table->json('departments');
            $table->json('services');
            $table->json('operating_hours')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospitals');
    }
};
