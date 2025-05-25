<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterestedUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'state_id',
        'village_id',
        'city_id',
        'name',
        'email',
        'contact_number',
        'village_name',
        'pincode',
        'district',
        'area_of_land',
        'land_unit',
        'type'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function slots()
    {
        return $this->hasMany(Slot::class);
    }
    public function village()
    {
        return $this->belongsTo(Village::class);
    }
}
