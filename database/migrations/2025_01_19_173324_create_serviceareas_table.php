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
            $table->unsignedBigInteger('service_id'); // Service ID
            $table->unsignedBigInteger('area_id'); // Area ID
            $table->timestamps();
        
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
