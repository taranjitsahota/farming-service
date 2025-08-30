<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tractor extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $fillable = ['partner_id', 'name', 'substation_id', 'registration_no', 'status'];
    protected $casts = ['status' => 'string'];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
    public function substation()
    {
        return $this->belongsTo(Substation::class);
    }
    public function unavailability()
    {
        return $this->hasMany(TractorUnavailability::class);
    }
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // Optional compatibility pivot
    public function compatibleTypes()
    {
        return $this->belongsToMany(EquipmentType::class, 'tractor_type_compatibility');
    }

    public function scopeActive($q)
    {
        return $q->where('status', 'active');
    }
}
