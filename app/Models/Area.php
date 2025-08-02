<?php

namespace App\Models;

use App\Models\Scopes\ServiceRoleScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Area extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['tehsil_id','substation_id' , 'district_id', 'state_id', 'village_id', 'pincode', 'village_id', 'is_enabled'];

     protected static function booted()
    {
        static::addGlobalScope(new ServiceRoleScope);
    }

    public function tehsil()
    {
        return $this->belongsTo(Tehsil::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
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

    public function substation()
    {
        return $this->belongsTo(Substation::class);
    }
}
