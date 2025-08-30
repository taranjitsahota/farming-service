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
        Schema::create('unit_area_coverage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('equipment_units')->cascadeOnDelete();
            $table->foreignId('area_id')->constrained('areas')->cascadeOnDelete();
            $table->boolean('is_enabled')->default(true)->index();
            $table->timestamps();

            $table->unique(['unit_id', 'area_id'], 'uac_unique');
            $table->index(['area_id', 'is_enabled'], 'uac_area_enabled_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_area_coverage');
    }
};
