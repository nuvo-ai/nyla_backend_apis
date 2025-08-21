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
        if (!Schema::hasTable('hospital_emrs')) {
            Schema::create('hospital_emrs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('hospital_id')->constrained('hospitals')->onDelete('cascade');
                $table->foreignId('patient_id')->constrained('hospital_patients')->onDelete('cascade');
                $table->enum('status', ['active', 'discharged', 'admitted'])->default('active');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospital_emrs');
    }
};
