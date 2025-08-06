<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medication_dosages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medication_id')->constrained('medications')->onDelete('cascade');
            $table->string('strength'); // e.g., "500mg", "10mg", "250mg/5ml"
            $table->string('form'); // e.g., "tablet", "capsule", "liquid", "injection"
            $table->string('unit'); // e.g., "mg", "ml", "mcg"
            $table->decimal('quantity', 10, 2)->nullable(); // e.g., 500, 10, 250
            $table->string('frequency')->nullable(); // e.g., "once daily", "twice daily", "as needed"
            $table->text('instructions')->nullable(); // e.g., "Take with food", "Take on empty stomach"
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_dosages');
    }
};
