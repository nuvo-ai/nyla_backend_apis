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
        Schema::table('medication_reminders', function (Blueprint $table) {
            $table->longText('notes')->after('time')->nullable()->comment('Additional notes for the reminder');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medication_reminders', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
};
