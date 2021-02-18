<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'student_name',
        'cpr',
        'email',
        'mobile',
        'mobile2',
        'dob',
        'address',
        'live_inـstate',
        'relationshipـstate',
        'family_members',
        'family_depends',
        'degree',
        'hawzaـhistory',
        'hawzaـhistory_details',
        'healthـhistory',
        'healthـhistory_details',
        'financialـstate',
        'financial_details',
        'student_notes',
        'registration_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'cpr' => 'integer',
        'mobile' => 'integer',
        'mobile2' => 'integer',
        'dob' => 'date',
        'hawzaـhistory' => 'boolean',
        'healthـhistory' => 'boolean',
        'registration_at' => 'date',
    ];


    protected $appends = [
        'long_name'
    ];

    public function classRooms()
    {
        return $this->hasMany(\App\Models\ClassRoom::class);
        // return $this->belongsToMany(\App\Models\ClassRoom::class);
    }

    public function getLongNameAttribute()
    {
        return "[" . $this->cpr . "] " . $this->student_name . " [" . $this->mobile . "]";
    }
}
