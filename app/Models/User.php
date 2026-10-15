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
    use \App\Models\Concerns\SignsOutDevicesOnPasswordChange;
    use HasFactory, Notifiable;
    use CrudTrait; // <----- this
    use HasRoles; // <------ and this

    /**
     * Pins Spatie role/permission lookups to the 'web' guard.
     *
     * This model is the provider for three guards (web, api, backpack),
     * and Backpack's admin middleware switches the app's default guard to
     * 'backpack' for admin requests. Without this, Spatie's guard
     * resolution picks whichever of those guards is currently active,
     * so role checks silently look for roles under the wrong guard_name
     * depending on where in the app the check runs.
     *
     * @var string
     */
    protected $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
    ];

    /**
     * Sign-in name (admin panel and portal); the email is contact information only.
     * Stored lowercase, so the login can match it exactly.
     */
    const USERNAME_PATTERN = '/^[a-z0-9._-]+$/i';

    /**
     * Validation for a new or changed username, shared by the admin users CRUD and the
     * teacher's portal profile. Input is lowercased by the mutator below before saving.
     *
     * @param  int|null  $ignoreId  the user being updated
     * @return array
     */
    public static function usernameRules($ignoreId = null)
    {
        return [
            'required',
            'string',
            'min:3',
            'max:50',
            'regex:' . self::USERNAME_PATTERN,
            \Illuminate\Validation\Rule::unique('users', 'username')->ignore($ignoreId),
        ];
    }

    /**
     * Contact email: optional, unique when set (sign-in uses the username).
     *
     * @param  int|null  $ignoreId  the user being updated
     * @return array
     */
    public static function emailRules($ignoreId = null)
    {
        return [
            'nullable',
            'email',
            'max:255',
            \Illuminate\Validation\Rule::unique('users', 'email')->ignore($ignoreId),
        ];
    }

    public function setUsernameAttribute($value)
    {
        $this->attributes['username'] = $value === null ? null : strtolower(trim($value));
    }

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
