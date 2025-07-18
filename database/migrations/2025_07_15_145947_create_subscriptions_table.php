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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('plans')->onDelete('cascade');

            $table->string('subscription_code')->unique();
            $table->string('email_token')->nullable();
            $table->string('customer_code')->nullable(); 

            $table->foreignId('payment_gateway_id')->nullable()->constrained('payment_gateways')->onDelete('cascade');
            $table->string('payment_method')->nullable(); // e.g., card, bank transfer

            $table->string('status')->default(StatusConstants::PENDING);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('authorization_reusable')->default(false);
            $table->string('next_payment_date')->nullable(); // from webhook or fetch

            $table->json('meta')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
