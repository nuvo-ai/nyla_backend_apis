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
        if (!Schema::hasColumn('hospital_appointments', 'doctor_id')) {
            Schema::table('hospital_appointments', function (Blueprint $table) {
                $table->dropForeign(['doctor_id']);
                $table->foreign('doctor_id')->references('id')->on('doctors')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('hospital_appointments', 'doctor_id')) {
            Schema::table('hospital_appointments', function (Blueprint $table) {
                $table->dropForeign(['doctor_id']);
                $table->foreign('doctor_id')->references('id')->on('hospital_users')->cascadeOnDelete();
            });
        }
    }
};
