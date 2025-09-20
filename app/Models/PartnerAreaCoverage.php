<?php

namespace App\Models;

use App\Models\Scopes\ServiceRoleScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerAreaCoverage extends Model
{
    use HasFactory;
    protected $table = 'partner_area_coverage';
    protected $fillable = ['partner_id', 'area_id', 'is_enabled'];
    // protected $casts = ['is_enabled' => 'boolean'];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function scopeEnabled($q)
    {
        return $q->where('is_enabled', true);
    }
     protected static function booted()
    {
        static::addGlobalScope(new ServiceRoleScope);
    }
}
