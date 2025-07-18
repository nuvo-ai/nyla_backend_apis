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
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->enum('type', ['private', 'public', 'teaching', 'specialist', 'general']);
            $table->string('registration_number');
            $table->string('phone');
            $table->string('email')->unique();
            $table->string('logo_path')->nullable();
            $table->text('street_address');
            $table->string('city');
            $table->string('state');
            $table->string('country');
            $table->text('google_maps_location')->nullable();
            $table->integer('number_of_beds')->nullable();
            $table->string('license_path')->nullable();
            $table->boolean('request_onsite_setup')->default(false);
            $table->boolean('terms_accepted')->default(false);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
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
