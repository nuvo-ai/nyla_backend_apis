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
        if (!Schema::hasColumn('hospital_patients', 'chief_complaints')) {
            Schema::table('hospital_patients', function (Blueprint $table) {
                $table->longText('chief_complaints')->after('oxygen_saturation');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('hospital_patients', 'chief_complaints')) {
            Schema::table('hospital_patients', function (Blueprint $table) {
                $table->dropColumn('chief_complaints');
            });
        }
    }
};
