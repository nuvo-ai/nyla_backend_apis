<?php

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
        if (!Schema::hasTable('hospital_patients')) {
            Schema::create('hospital_patients', function (Blueprint $table) {
                $table->id();
                $table->foreignId('hospital_id')->constrained('hospitals')->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('doctor_id')->nullable()->constrained('hospital_users')->nullOnDelete();
                $table->string('temperature')->nullable();
                $table->string('weight')->nullable();
                $table->string('height')->nullable();
                $table->string('blood_pressure')->nullable();
                $table->string('heart_rate')->nullable();
                $table->string('respiratory_rate')->nullable();
                $table->string('oxygen_saturation')->nullable();
                $table->date('last_visit')->nullable();
                $table->string('status')->default(StatusConstants::ACTIVE);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospital_patients');
    }
};
