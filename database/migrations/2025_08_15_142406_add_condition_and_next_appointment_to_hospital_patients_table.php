<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hospital_patients', function (Blueprint $table) {
            $table->text('condition')->nullable()->after('chief_complaints');
            $table->dateTime('next_appointment')->nullable()->after('condition');
        });
    }

    public function down(): void
    {
        Schema::table('hospital_patients', function (Blueprint $table) {
            $table->dropColumn(['condition', 'next_appointment']);
        });
    }
};
