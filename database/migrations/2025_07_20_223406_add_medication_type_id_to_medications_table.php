<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medications', function (Blueprint $table) {
            $table->foreignId('medication_type_id')->nullable()->constrained('medication_types')->onDelete('set null')->after('pharmacy_id');
        });
    }

    public function down(): void
    {
        Schema::table('medications', function (Blueprint $table) {
            $table->dropForeign(['medication_type_id']);
            $table->dropColumn('medication_type_id');
        });
    }
}; 