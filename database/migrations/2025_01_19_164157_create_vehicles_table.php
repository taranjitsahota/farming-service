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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_name'); // Name or model of the vehicle
            $table->string('vehicle_number')->unique(); // Unique vehicle registration number
            $table->string('type'); // Type of vehicle (e.g., tractor, combine)
            $table->boolean('is_enabled')->default(true); // Enable/Disable status
            $table->unsignedBigInteger('pincode')->nullable(); // Specific area of service
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
