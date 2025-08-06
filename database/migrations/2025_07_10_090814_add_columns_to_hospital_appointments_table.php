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
        if (!Schema::hasColumn('hospital_appointments', 'note')) {
            Schema::table('hospital_appointments', function (Blueprint $table) {
                $table->string('note')->nullable()->after('status');
                $table->string('source')->nullable()->after('note');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('hospital_appointments', 'note')) {
            Schema::table('hospital_appointments', function (Blueprint $table) {
                $table->dropColumn(['note', 'source']);
            });
        }
    }
};
