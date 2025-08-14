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
        Schema::create('visit_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->string('patient_id')->constrained('hospital_patients')->onDelete('cascade');
            $table->string('diagnosis_and_assessment')->nullable();
            $table->string('treatment_plan_and_recommendation')->nullable();
            $table->date('visit_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visit_notes');
    }
};
