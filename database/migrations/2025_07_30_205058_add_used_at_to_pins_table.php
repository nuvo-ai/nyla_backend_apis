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
        Schema::table('pins', function (Blueprint $table) {
            $table->timestamp('used_at')->nullable()->after('expires_at')
                ->comment('Timestamp when the PIN was used');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pins', function (Blueprint $table) {
            $table->dropColumn('used_at');
        });
    }
};
