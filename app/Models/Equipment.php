<?php

namespace App\Models;

use App\Models\Scopes\ServiceRoleScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;

    protected $table = 'equipments';
    protected $fillable = [
        'name',
        'substation_id',
        'service_id',
        'image',
        'price_per_kanal',
        'min_kanal',
        'image',
        'minutes_per_kanal',
        'inventory',
        'is_enabled',
    ];

    // protected static function booted()
    // {
    //     static::addGlobalScope(new ServiceRoleScope);
    // }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    // public function substation()
    // {
    //     return $this->belongsTo(Substation::class);
    // }
    // public function serviceArea()
    // {
    //     return $this->hasMany(ServiceArea::class);
    // }
}
