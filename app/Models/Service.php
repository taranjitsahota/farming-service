<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'category' , 'substation_id' , 'equipment_id', 'is_enabled'];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }
    
     public function substation()
    {
        return $this->belongsTo(Substation::class);
    }
}
