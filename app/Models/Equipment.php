<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;

    protected $table = 'equipments';
    protected $fillable = [
        'name',
        'substation_id',
        'image',
        'price_per_kanal',
        'min_kanal',
        'image',
        'minutes_per_kanal',
        'inventory',
        'is_enabled',
    ];
    public function services()
    {
        return $this->hasMany(Service::class);
    }
    public function substation()
    {
        return $this->belongsTo(Substation::class);
    }
}
