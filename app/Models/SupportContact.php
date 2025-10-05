<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportContact extends Model
{
    use HasFactory;

     protected $fillable = [
        'email',
        'phone',
        'whatsapp_url',
    ];
}
