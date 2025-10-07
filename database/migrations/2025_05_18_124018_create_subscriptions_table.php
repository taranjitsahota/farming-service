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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('subscription_plan_id')->nullable();
            $table->string('razorpay_subscription_id')->unique();
            $table->integer('kanals');
            $table->decimal('land_area', 8, 2);
            $table->decimal('total_price', 8, 2);
            $table->decimal('price_per_kanal', 8, 2);
            $table->string('location')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamp('next_billing_date')->nullable();
            $table->enum('status', [
                'created',     // just created, not yet activated
                'active',      // active and paid
                'past_due',    // payment failed, awaiting retry
                'paused',      // paused manually
                'cancelled',   // cancelled by admin or user
                'completed',   // completed after all cycles
            ])->default('created');
            $table->timestamps();
            $table->foreign('subscription_plan_id')
                  ->references('id')
                  ->on('subscription_plans')
                  ->onDelete('set null');
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
