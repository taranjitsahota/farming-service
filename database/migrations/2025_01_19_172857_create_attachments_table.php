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
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->string('attachment_name'); // Name of the attachment
            $table->string('attachment_type'); // Type of attachment (e.g., plow, harrow)
            $table->unsignedBigInteger('vehicle_id')->nullable(); // Relation to vehicles
            $table->boolean('is_enabled')->default(true); // Enable/Disable status
            $table->timestamps();

            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};