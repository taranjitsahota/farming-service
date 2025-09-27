<?php

namespace App\Models;

use App\Models\Scopes\ServiceRoleScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'area_id', 
        'equipment_type_id',
        'partner_id',
        'driver_id',
        'tractor_id', 
        'equipment_unit_id',
        'substation_id',
        'crop_id',
        'land_area',
        'slot_date',
        'start_time',
        'end_time', 
        'duration_minutes',
        'address',
        'latitude',
        'longitude',
        'user_note',
        'admin_note',
        'price',
        'price_per_kanal',
        'payment_method',
        'payment_id',
        'reserved_until',
        'paid_at',
        'cancelled_at',
        'cancel_reason',
        'refund_amount',
        'refund_status',
        'payment_status',
        'booking_status'
    ];

    protected $casts = [
        'slot_date' => 'date',
        // 'start_time' => 'datetime:Y-m-d H:i',
        // 'end_time' => 'datetime:Y-m-d H:i', 
        'land_area' => 'decimal:2',
        'price' => 'decimal:2',
        'price_per_kanal' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'refund_amount' => 'decimal:2',
        'reserved_until' => 'datetime',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new ServiceRoleScope);
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function equipmentType()
    {
        return $this->belongsTo(EquipmentType::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function tractor()
    {
        return $this->belongsTo(Tractor::class);
    }

    public function units()
    {
        return $this->belongsTo(EquipmentUnit::class, 'equipment_unit_id');
    }

    public function substation()
    {
        return $this->belongsTo(Substation::class);
    }

    public function crop()
    {
        return $this->belongsTo(Crop::class);
    }

    // Scopes
    public function scopeConfirmed($query)
    {
        return $query->where('payment_status', 'confirmed');
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->where('payment_status', 'confirmed')
              ->orWhere(function ($q2) {
                  $q2->where('payment_status', 'pending')
                     ->where('reserved_until', '>', now());
              });
        });
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('slot_date', $date);
    }

    public function scopeForArea($query, $areaId)
    {
        return $query->where('area_id', $areaId);
    }

    public function scopeForEquipmentType($query, $equipmentTypeId)
    {
        return $query->where('equipment_type_id', $equipmentTypeId);
    }

    // Accessors & Mutators
    public function getStartTimeAttribute($value)
    {
        return Carbon::parse($value)->format('H:i');
    }

    public function getEndTimeAttribute($value)
    {
        return Carbon::parse($value)->format('H:i');
    }

    // Helper methods
    public function isActive()
    {
        return $this->payment_status === 'confirmed' || 
               ($this->payment_status === 'pending' && $this->reserved_until > now());
    }

    public function isExpired()
    {
        return $this->payment_status === 'pending' && $this->reserved_until <= now();
    }

    public function canBeCancelled()
    {
        return in_array($this->booking_status, ['pending', 'confirmed']) && 
               !$this->cancelled_at;
    }

    public function getFormattedSlotTime()
    {
        return $this->start_time . ' - ' . $this->end_time;
    }

    public function getTotalDurationInHours()
    {
        if ($this->duration_minutes) {
            return round($this->duration_minutes / 60, 2);
        }
        
        $start = Carbon::parse($this->slot_date . ' ' . $this->start_time);
        $end = Carbon::parse($this->slot_date . ' ' . $this->end_time);
        return $start->diffInMinutes($end) / 60;
    }
}