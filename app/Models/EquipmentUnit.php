<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EquipmentUnit extends Model
{
    use HasFactory;
    // use SoftDeletes;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($unit) {
            // Get prefix from equipment type name (short code)
            $prefix = strtoupper(substr($unit->equipmentType->equipment->name ?? 'EQ', 0, 3)); // e.g. "SUP" for "Super Seeder"

            // Count existing units for this type
            $count = EquipmentUnit::where('equipment_type_id', $unit->equipment_type_id)->count() + 1;

            // Generate serial number (prefix + padded counter)
            $unit->serial_no = $prefix . str_pad($count, 4, '0', STR_PAD_LEFT); 
            // e.g. "SUP0001"
        });
    }

    protected $fillable = [
        'partner_id',
        'equipment_type_id',
        'substation_id',
        // 'tractor_id',
        'serial_no',
        'status',
        'meta'
    ];
    protected $casts = ['meta' => 'array'];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
    public function equipmentType()
    {
        return $this->belongsTo(EquipmentType::class);
    }
    public function substation()
    {
        return $this->belongsTo(Substation::class);
    }
    public function tractor()
    {
        return $this->belongsTo(Tractor::class);
    }
    public function unavailability()
    {
        return $this->hasMany(EquipmentUnavailability::class, 'unit_id');
    }
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'unit_id');
    }

    // Optional per-unit coverage (if you enable that table)
    public function unitCoverages()
    {
        return $this->hasMany(UnitAreaCoverage::class, 'unit_id');
    }

    public function scopeActive($q)
    {
        return $q->where('status', 'active');
    }
}
