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
        Schema::create('partner_unavailability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained('partners')->cascadeOnDelete();
            $table->timestamp('start_at')->index();
            $table->timestamp('end_at')->index();
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index(['partner_id', 'start_at', 'end_at'], 'partner_unavail_window_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partner_unavailability');
    }
};
