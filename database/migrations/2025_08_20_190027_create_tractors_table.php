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
        Schema::create('tractors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('partner_id')->constrained('partners')->cascadeOnDelete();
            $table->string('registration_no')->unique();
            $table->enum('status', ['active', 'maintenance', 'retired'])->default('active')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['partner_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tractors');
    }
};
