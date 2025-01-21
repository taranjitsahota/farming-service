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
        Schema::create('slots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // User who booked the slot
            $table->unsignedBigInteger('crop_id'); // Crop to be serviced
            $table->unsignedBigInteger('attachment_id'); // Attachment selected
            $table->unsignedBigInteger('vehicle_id'); // Vehicle selected
            $table->unsignedBigInteger('service_id'); // Service selected
            $table->unsignedBigInteger('area_id'); // Reference to area where service will be provided
            $table->date('slot_date'); // Slot date
            $table->time('start_time'); // Start time
            $table->time('end_time'); // End time
            $table->enum('status', ['pending', 'confirmed', 'completed', 'canceled'])->default('pending'); // Slot status
            $table->text('user_note')->nullable(); // User's additional notes
            $table->timestamps();
        
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('crop_id')->references('id')->on('crops')->onDelete('cascade');
            $table->foreign('attachment_id')->references('id')->on('attachments')->onDelete('cascade');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->foreign('area_id')->references('id')->on('areas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slots');
    }
};
