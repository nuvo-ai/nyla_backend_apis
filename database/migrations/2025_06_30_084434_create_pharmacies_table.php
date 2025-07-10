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
        Schema::create('pharmacies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('license_number')->unique();
            $table->string('pharmacist_in_charge_name');
            $table->string('phone');
            $table->string('email');
            $table->string('logo_path')->nullable();
            $table->string('street_address');
            $table->string('city');
            $table->string('state');
            $table->string('country');
            $table->string('google_maps_location')->nullable();
            $table->boolean('delivery_available')->default(false);
            $table->string('nafdac_certificate');
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
        Schema::dropIfExists('pharmacies');
    }
};
