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
        Schema::create('tractor_unavailability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tractor_id')->constrained('tractors')->cascadeOnDelete();
            $table->timestamp('start_at')->index();
            $table->timestamp('end_at')->index();
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index(['tractor_id', 'start_at', 'end_at'], 'tractor_unavail_window_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tractor_unavailability');
    }
};
