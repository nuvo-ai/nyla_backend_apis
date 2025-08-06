<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pharmacy_id')->constrained('pharmacies')->onDelete('cascade');
                $table->foreignId('patient_id')->nullable()->constrained('users')->onDelete('set null');
                $table->enum('priority', ['urgent', 'normal'])->default('normal');
                $table->enum('status', ['pending', 'processing', 'accepted', 'completed', 'delivered', 'dispensed', 'declined'])->default('pending');
                $table->decimal('total_price', 12, 2);
                $table->string('prescription_url')->nullable();
                $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
