<?php

namespace App\Models;

use App\Models\Scopes\ServiceRoleScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'is_enabled'];

    public function equipment()
    {
        return $this->hasMany(Equipment::class);
    }
    public function equipmentTypes()
    {
        return $this->hasMany(EquipmentType::class);
    }
}
