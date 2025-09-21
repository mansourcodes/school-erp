<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    use CrudTrait; // <----- this
    use HasRoles; // <------ and this

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function classRooms()
    {
        return $this->belongsToMany(ClassRoom::class, 'class_room_teacher');
    }



    public function getAvatarUrlAttribute()
    {

        return "https://api.dicebear.com/7.x/shapes/svg?seed=" . urlencode($this->username);
        // return "https://api.dicebear.com/7.x/thumbs/svg?seed=" . urlencode($this->username);
        // return "https://api.dicebear.com/7.x/initials/svg?seed=" . urlencode($this->username);
    }
}
