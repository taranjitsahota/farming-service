<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceArea extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'serviceareas'; 
    
    protected $fillable = ['service_id' , 'substation_id' , 'area_id','is_enabled'];    

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

     public function substation()
    {
        return $this->belongsTo(Substation::class);
    }

}
