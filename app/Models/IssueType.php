<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IssueType extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'issue_type_id',
        'message',
        'image',
    ];

    public function issueType()
    {
        return $this->belongsTo(IssueType::class, 'issue_type_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
