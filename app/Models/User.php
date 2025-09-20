<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $guarded = [];

    // Optional: define guard
    protected $guard_name = 'api';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'is_verified',
        'profile_photo_url',
        'profile_photo_path',
        'substation_id',
    ];
    
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function substation()
    {
        return $this->belongsTo(Substation::class);
    }

    public function partner()
    {
        return $this->hasOne(Partner::class);
    }

    public function driver()
    {
        return $this->hasOne(Driver::class);
    }
    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }
}
