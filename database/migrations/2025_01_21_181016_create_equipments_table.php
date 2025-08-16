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
        Schema::create('equipments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('substation_id')->nullable();
            $table->unsignedBigInteger("service_id")->nullable();
            $table->string('name');
            $table->string('image')->nullable();
            $table->decimal('price_per_kanal', 8, 2);
            $table->integer('min_kanal');
            $table->integer('minutes_per_kanal');
            $table->integer('inventory');
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
            
            $table->foreign('substation_id')->references('id')->on('substations')->onDelete('set null');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipments');
    }
};
