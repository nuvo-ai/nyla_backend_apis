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
        if (!Schema::hasTable('electronic_medical_records')) {
            Schema::create('electronic_medical_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('hospital_patient_id')->constrained()->onDelete('cascade');
                $table->foreignId('doctor_id')->nullable()->constrained('hospital_users')->nullOnDelete();
                $table->text('chief_complaints')->nullable();
                $table->text('diagnosis')->nullable();
                $table->text('treatment_plan')->nullable();
                $table->text('notes')->nullable();
                $table->json('lab_results')->nullable();
                $table->json('prescriptions')->nullable();
                $table->date('visit_date')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('electronic_medical_records');
    }
};
