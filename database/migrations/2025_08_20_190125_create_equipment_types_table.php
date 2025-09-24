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
        Schema::create('equipment_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('equipment_id')->nullable()->constrained('equipments')->nullOnDelete();
            $table->boolean('requires_tractor')->default(true)->index();
            // $table->boolean('is_self_propelled')->default(false)->index();
            $table->unsignedInteger('minutes_per_kanal')->default(4);
            $table->decimal('price_per_kanal', 8, 2)->default(0);
            $table->unsignedInteger('min_kanal')->default(0);
            $table->string('image')->nullable();
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_types');
    }
};
