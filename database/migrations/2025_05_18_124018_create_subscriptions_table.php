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
            $table->string('razorpay_subscription_id')->unique();
            $table->enum('plan_type', ['general', 'sugarcane']);
            $table->integer('kanals');
            $table->decimal('price_per_kanal', 8, 2);
            $table->integer('kanals_used')->default(0);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status',['created', 'active', 'cancelled', 'completed'])->default('active');
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
