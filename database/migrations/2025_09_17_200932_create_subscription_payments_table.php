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
        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
                $table->unsignedBigInteger('subscription_id');
            $table->foreign('subscription_id')
                  ->references('id')
                  ->on('subscriptions')
                  ->onDelete('cascade');

            $table->string('razorpay_payment_id')->unique();
            $table->decimal('amount', 10, 2); // INR with paise handled
            $table->string('currency')->default('INR');

            // Payment status from Razorpay (captured, failed, refunded, etc.)
            $table->string('status');

            $table->timestamp('paid_at')->nullable();

            // Store raw webhook/payment payload for debugging/audit
            $table->json('payload')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
    }
};
