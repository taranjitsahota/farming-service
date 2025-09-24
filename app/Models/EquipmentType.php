<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EquipmentType extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'service_id',
        'equipment_id',
        'requires_tractor',
        // 'is_self_propelled',
        'minutes_per_kanal',
        'min_kanal',
        'price_per_kanal',
        'image',
    ];
    // protected $casts = [
    //     'requires_tractor' => 'boolean',
    //     'is_self_propelled' => 'boolean',
    //     'is_enabled'       => 'boolean',
    // ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    public function units()
    {
        return $this->hasMany(EquipmentUnit::class);
    }
    public function coverages()
    {
        return $this->hasMany(PartnerAreaCoverage::class);
    }
    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
}
