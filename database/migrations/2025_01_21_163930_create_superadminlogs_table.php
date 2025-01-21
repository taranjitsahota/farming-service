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
        Schema::create('superadminlogs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // ID of the user who performed the action
            $table->string('action'); // Description of the action performed (e.g., 'Enable Vehicle')
            $table->string('target_type'); // The type of resource affected (e.g., 'vehicle', 'service', 'area')
            $table->unsignedBigInteger('target_id')->nullable(); // ID of the resource affected
            $table->json('previous_data')->nullable(); // Data before the change (stored as JSON)
            $table->json('new_data')->nullable(); // Data after the change (stored as JSON)
            $table->ipAddress('ip_address')->nullable(); // IP address of the user performing the action
            $table->text('additional_info')->nullable(); // Any extra context (optional)
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('superadminlogs');
    }
};
