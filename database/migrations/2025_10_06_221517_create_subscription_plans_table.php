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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('price_per_kanal');
            $table->integer('min_kanals')->default(1);
            $table->integer('upfront_percentage')->default(25);
            $table->integer('emi_months')->default(11);
            $table->string('razorpay_plan_id')->nullable();
            $table->json('services')->nullable();
            $table->json('benefits')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
