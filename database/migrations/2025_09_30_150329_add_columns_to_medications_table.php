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
        if(Schema::hasTable('medications')) {
            Schema::table('medications', function (Blueprint $table) {
                if (!Schema::hasColumn('medications', 'manufacturer')) {
                    $table->string('manufacturer')->nullable()->after('is_active');
                }
                if (!Schema::hasColumn('medications', 'expiry_date')) {
                    $table->date('expiry_date')->nullable()->after('manufacturer');
                }
                if (!Schema::hasColumn('medications', 'batch_number')) {
                    $table->string('batch_number')->nullable()->after('expiry_date');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medications', function (Blueprint $table) {
            if (Schema::hasColumn('medications', 'manufacturer')) {
                $table->dropColumn('manufacturer');
            }
            if (Schema::hasColumn('medications', 'expiry_date')) {
                $table->dropColumn('expiry_date');
            }
            if (Schema::hasColumn('medications', 'batch_number')) {
                $table->dropColumn('batch_number');
            }
        });
    }
};
