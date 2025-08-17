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
            $table->unsignedBigInteger('substation_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('crop_id');
            $table->unsignedBigInteger('service_area_id');
            $table->integer('driver_id')->nullable();
            $table->integer('land_area');
            $table->date('slot_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('duration')->nullable();
            $table->string('address')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->text('user_note')->nullable(); 
            $table->text('admin_note')->nullable(); 
            $table->decimal('price', 10, 2);
            $table->string('payment_method')->nullable();
            $table->string('payment_id')->nullable();
            $table->datetime('reserved_until')->nullable();
            $table->datetime('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->string('refund_status')->nullable();
            $table->enum('payment_status', ['pending', 'confirmed','cancelled'])->default('pending');
            $table->enum('booking_status', ['pending', 'completed','cancelled'])->default('pending');
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('substation_id')->references('id')->on('substations')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('crop_id')->references('id')->on('crops')->onDelete('cascade');
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
