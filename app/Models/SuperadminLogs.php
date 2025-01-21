<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuperadminLogs extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'action', 'target_type', 
        'target_id', 'previous_data', 'new_data', 'ip_address','additional_info'
    ];

}
