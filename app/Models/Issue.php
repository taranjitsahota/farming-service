<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{
    use HasFactory;

     protected $fillable = [
        'user_id',
        'issue_type_id',
        'title',
        'description',
    ];

    public function issues()
    {
        return $this->hasMany(Issue::class, 'issue_type_id');
    }
}
