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
        Schema::create('health_records', function (Blueprint $table) {
            $table->id(); 
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('blood_pressure')->nullable(); // e.g. 120/80
            $table->string('heart_rate')->nullable();     // e.g. 72 bpm
            $table->float('weight')->nullable();          // in kg
            $table->float('height')->nullable();          // in cm
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_records');
    }
};
