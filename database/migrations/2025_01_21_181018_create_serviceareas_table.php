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
        Schema::create('serviceareas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('substation_id')->nullable();
            $table->unsignedBigInteger('service_id');
            $table->unsignedBigInteger('area_id');
            $table->boolean('is_enabled')->default(true);
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('substation_id')->references('id')->on('substations')->onDelete('set null');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->foreign('area_id')->references('id')->on('areas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('serviceareas');
    }
};
