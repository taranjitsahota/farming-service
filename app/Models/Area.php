<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Area extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['city_id', 'state_id', 'village_id', 'pincode', 'village_id', 'is_enabled'];

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
