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
        Schema::create('servicelogs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('slot_id'); // Reference to the slot
            $table->unsignedBigInteger('user_id'); // Reference to the user receiving the service
            $table->unsignedBigInteger('vehicle_id'); // Reference to the vehicle used
            $table->unsignedBigInteger('service_id'); // Reference to the service provided
            $table->text('notes')->nullable(); // Optional notes for the service
            $table->decimal('amount_paid', 10, 2)->default(0.00); // Payment for the service
            $table->enum('status', ['completed', 'in_progress', 'canceled'])->default('in_progress'); // Status of the service
            $table->timestamps();
        
            $table->foreign('slot_id')->references('id')->on('slots')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicelogs');
    }
};
