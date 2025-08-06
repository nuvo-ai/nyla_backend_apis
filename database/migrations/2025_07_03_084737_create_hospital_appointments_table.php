<?php

use App\Constants\General\AppConstants;
use App\Constants\General\StatusConstants;
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
        if (!Schema::hasTable('hospital_appointments')) {
            Schema::create('hospital_appointments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('hospital_id')->constrained('hospitals')->cascadeOnDelete();
                $table->foreignId('scheduler_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('doctor_id')->nullable()->constrained('doctors')->cascadeOnDelete();
                $table->string('patient_name');
                $table->string('appointment_type'); // e.g., consultation, follow-up, emergency
                $table->date('appointment_date');
                $table->time('appointment_time');
                $table->string('status')->default(StatusConstants::PENDING); // pending, confirmed, cancelled, completed
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospital_appointments');
    }
};
