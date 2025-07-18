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
        Schema::table('hospital_patients', function (Blueprint $table) {
            $table->string('emergency_contact_name')->nullable()->after('last_visit');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            $table->json('current_symptoms')->nullable()->after('emergency_contact_phone');
            $table->integer('pain_level')->nullable()->after('current_symptoms');
            $table->json('know_allergies')->nullable()->after('pain_level');
            $table->string('visit_priority')->default('normal')->after('know_allergies');
            $table->string('medical_history')->nullable()->after('visit_priority');
            $table->json('current_medications')->nullable()->after('medical_history');
            $table->text('insurance_info')->nullable()->after('current_medications');
            $table->string('visit_type')->nullable()->after('insurance_info');
            $table->string('referral_source')->nullable()->after('visit_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hospital_patients', function (Blueprint $table) {
            //
        });
    }
};
