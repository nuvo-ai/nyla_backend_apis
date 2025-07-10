<?php

use App\Constants\General\AppConstants;
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
        Schema::create('hospital_users', function (Blueprint $table) {
            $table->id();
             $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('hospital_id')->nullable()->constrained('hospitals')->cascadeOnDelete();
            $table->string('role')->default(AppConstants::ROLE_ADMIN);
            $table->foreignId('user_account_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospital_users');
    }
};
