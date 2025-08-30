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
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('area_id')->constrained('areas')->cascadeOnDelete();
            $table->foreignId('equipment_type_id')->constrained('equipment_types')->cascadeOnDelete();
            $table->foreignId('partner_id')->nullable()->constrained('partners')->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->foreignId('tractor_id')->nullable()->constrained('tractors')->nullOnDelete();
            $table->foreignId('equipment_unit_id')->nullable()->constrained('equipment_units')->nullOnDelete();
            $table->foreignId('substation_id')->nullable()->constrained('substations')->nullOnDelete();
            $table->foreignId('crop_id')->nullable()->constrained('crops')->nullOnDelete();
            
            // Booking details
            $table->decimal('land_area', 8, 2); // Changed to decimal for more precision
            $table->date('slot_date')->index();
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('duration_minutes')->nullable(); // Store duration in minutes
            
            // Location details
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Notes
            $table->text('user_note')->nullable();
            $table->text('admin_note')->nullable();
            
            // Pricing
            $table->decimal('price', 10, 2);
            $table->decimal('price_per_kanal', 8, 2)->nullable(); // Store the rate used
            
            // Payment details
            $table->string('payment_method')->nullable();
            $table->string('payment_id')->nullable();
            $table->timestamp('reserved_until')->nullable()->index();
            $table->timestamp('paid_at')->nullable();
            
            // Cancellation details
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->enum('refund_status', ['pending', 'processed', 'failed'])->nullable();
            
            // Status fields
            $table->enum('payment_status', ['pending', 'confirmed', 'cancelled', 'refunded'])->default('pending')->index();
            $table->enum('booking_status', ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'])->default('pending')->index();
            
            // Timestamps
            $table->softDeletes();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['slot_date', 'area_id', 'equipment_type_id'], 'booking_slot_lookup_idx');
            $table->index(['partner_id', 'slot_date'], 'partner_date_idx');
            $table->index(['payment_status', 'reserved_until'], 'payment_reservation_idx');
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