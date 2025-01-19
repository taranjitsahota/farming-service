<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
        public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Foreign key to users table
            $table->foreignId('subscription_id')->constrained('usersubscriptions')->onDelete('cascade'); // Foreign key to user_subscriptions table
            $table->decimal('amount', 8, 2); // Amount paid
            $table->string('payment_method'); // e.g., 'credit_card', 'paypal'
            $table->string('payment_status')->default('pending'); // Payment status (pending, completed, failed)
            $table->string('transaction_id')->nullable(); // Payment gateway transaction ID
            $table->timestamp('payment_date')->nullable(); // Date and time of payment
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
