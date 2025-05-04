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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreignId('service_id');
            $table->unsignedBigInteger('crop_id');
            $table->unsignedBigInteger('service_area_id');
            $table->date('slot_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->text('user_note')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('payment_method')->nullable();
            $table->string('payment_id')->nullable();
            $table->datetime('reserved_until')->nullable();
            $table->datetime('paid_at')->nullable();
            $table->enum('status', ['pending', 'confirmed','cancelled'])->default('pending');
            $table->timestamps();
        
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('crop_id')->references('id')->on('crops')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->foreign('service_area_id')->references('id')->on('serviceareas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
