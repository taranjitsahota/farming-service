<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Substation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'is_enabled',
    ];
    
    public function equipments()
    {
        return $this->belongsToMany(Equipment::class);
    }
    
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function serviceareas()
    {
        return $this->hasMany(ServiceArea::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function areas()
    {
        return $this->hasMany(Area::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
