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
        Schema::create('usersubscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Foreign key to users table
            $table->foreignId('subscriptionplan_id')->constrained('subscriptionplans')->onDelete('cascade'); // Foreign key to subscription_plans table
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('active'); // e.g., active, expired, canceled
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->default('pending'); // Pending, paid, failed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usersubscriptions');
    }
};
