<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitAreaCoverage extends Model
{
    use HasFactory;
    protected $table = 'unit_area_coverage';
    protected $fillable = ['unit_id', 'area_id', 'is_enabled'];
    protected $casts = ['is_enabled' => 'boolean'];

    public function unit()
    {
        return $this->belongsTo(EquipmentUnit::class, 'unit_id');
    }
    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function scopeEnabled($q)
    {
        return $q->where('is_enabled', true);
    }
}
