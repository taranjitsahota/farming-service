<?php

namespace App\Models;

use App\Models\Scopes\ServiceRoleScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'company_name', 'gst_number', 'address', 'is_individual'];

    public function drivers()
    {
        return $this->hasMany(Driver::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function units()
    {
        return $this->hasMany(EquipmentUnit::class);
    }
    public function tractors()
    {
        return $this->hasMany(Tractor::class);
    }
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
    public function unavailability()
    {
        return $this->hasMany(PartnerUnavailability::class);
    }
    public function coverages()
    {
        return $this->hasMany(PartnerAreaCoverage::class);
    }

    public function scopeEnabled($q)
    {
        return $q->where('is_enabled', true);
    }
    public function areas()
    {
        return $this->belongsToMany(Area::class, 'partner_area_coverage')
            ->withPivot('is_enabled')
            ->withTimestamps();
    }
    protected static function booted()
    {
        static::addGlobalScope(new ServiceRoleScope);
    }
}
