<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInfo extends Model
{
    use HasFactory;

    protected $table = 'userinfos';
    
    protected $fillable = ['user_id', 'first_name', 'last_name', 'fathers_name','pincode','village','post_office','police_station','district','total_servicable_land'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
