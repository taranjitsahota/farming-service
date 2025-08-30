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
        Schema::create('equipment_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained('partners')->cascadeOnDelete();
            $table->foreignId('equipment_type_id')->constrained('equipment_types')->cascadeOnDelete();
            $table->foreignId('substation_id')->nullable()->constrained('substations')->nullOnDelete();
            // If permanently mounted on a tractor:
            $table->foreignId('tractor_id')->nullable()->constrained('tractors')->nullOnDelete();
            $table->string('serial_no')->nullable()->unique();
            $table->enum('status', ['active', 'maintenance', 'retired'])->default('active')->index();
            $table->json('meta')->nullable(); // optional: store price_per_kanal, image, etc.
            $table->timestamps();
            $table->softDeletes();

            $table->index(['partner_id', 'equipment_type_id', 'status'], 'unit_partner_type_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_units');
    }
};
