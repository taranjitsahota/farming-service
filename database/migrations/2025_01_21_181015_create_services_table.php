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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('substation_id')->nullable();
            $table->unsignedBigInteger('equipment_id');
            $table->string('category');
            $table->boolean('is_enabled')->default(true);
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('substation_id')->references('id')->on('substations')->onDelete('set null');
            $table->foreign('equipment_id')->references('id')->on('equipments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
