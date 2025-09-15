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
        Schema::create('driver_unavailability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained('partners')->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained('drivers')->cascadeOnDelete();
            $table->timestamp('start_at')->index();
            $table->timestamp('end_at')->index();
            $table->enum('leave_type', ['single_day', 'shift','long_leave']);
            $table->enum('shift', ['first', 'second'])->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();
            $table->index(['driver_id', 'start_at', 'end_at'], 'driver_unavail_window_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_unavailability');
    }
};
