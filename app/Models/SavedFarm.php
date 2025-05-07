<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedFarm extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'farm_id',
        'lattitude',
        'longitude',
        'address',
        'category',
    ];

}
