<?php

namespace App\Models;

use App\Models\Scopes\ServiceRoleScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'partner_id', 'license_number', 'experience_years', 'status'];

    protected $casts = [
        'experience_years' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function unavailability()
    {
        return $this->hasMany(DriverUnavailability::class);
    }
    protected static function booted()
    {
        static::addGlobalScope(new ServiceRoleScope);
    }
}
