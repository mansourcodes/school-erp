<?php

namespace App\Models;

use App\Models\Account\Payment;
use Carbon\Carbon;
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
        'financial_support_status',
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
        'long_name',
        'student_id',
        'courses',
        'age',
    ];

    public function classRooms()
    {
        // return $this->hasMany(\App\Models\ClassRoom::class);
        return $this->belongsToMany(\App\Models\ClassRoom::class);
    }

    public function payments()
    {
        return $this->hasMany(\App\Models\Account\Payment::class);
    }

    public function getLongNameAttribute()
    {
        $mobile2 = ($this->mobile2) ? '-' . $this->mobile2 : '';

        return "[" . $this->cpr . "] " . $this->student_name . " [" . $this->mobile . $mobile2 . "]";
    }

    public function getCoursesAttribute()
    {
        $list = [];
        foreach ($this->classRooms as $key => $classRoom) {
            $list[$classRoom->course->id] = $classRoom->course;
        }
        $courses = collect($list);
        return $courses;
    }

    public function getStudentIdAttribute()
    {

        $registration_at = ($this->registration_at) ?: $this->created_at;
        return ($registration_at->year .  str_pad($this->id, 4, '0', STR_PAD_LEFT));
    }

    public function getAgeAttribute()
    {
        if (isset($this->dob)) {
            $dob = new Carbon($this->dob);
            return $dob->diff(new Carbon())->y;
        } else if (strlen($this->cpr) <= 9) {

            $cpr = sprintf('%09d', $this->cpr);
            $month = substr($cpr, 2, 2);
            $year = substr($cpr, 0, 2);
            $year = ($year > 50) ? "19$year" : "20$year";
            $dob = new Carbon("$year-$month-01");
            return $dob->diff(new Carbon())->y;
        } else {
            return 0;
        }
    }



    public function getCoursesDropdown($crud = false)
    {

        $list = [];
        foreach ($this->classRooms as $key => $classRoom) {
            $list[] = [
                'label' => $classRoom->course->long_name,
                'url' => [
                    [
                        'label' => trans('reports.student_table'),
                        'url' => backpack_url('studentReports/SingleStudentTable' . '?course=' . $classRoom->course->id . '&student=' . $this->id),
                    ],
                    [
                        'label' => trans('reports.update_students_info'),
                        'url' => backpack_url('studentReports/SingleUpdateStudentsInfo' . '?course=' . $classRoom->course->id . '&student=' . $this->id),
                    ],
                ]

            ];
        }
        $data['list'] = $list;

        return view('vendor.backpack.crud.buttons.multilevel_dropdown', $data);
    }


    public function getUnpaidPaymentsDropdown($crud = false)
    {


        $payments = Payment::whereIn(
            'course_id',
            $this->courses->pluck('id')->toArray()
        )->where('student_id', $this->id)->get();


        $unpaid_courses = array_diff($this->courses->pluck('id')->toArray(), $payments->pluck('course_id')->toArray());


        $course_list = $this->courses->mapWithKeys(function ($item, $key) {
            return [$item['id'] => $item['long_name']];
        })->toArray();

        $list = [];
        foreach ($unpaid_courses as $key => $course_id) {
            $list[] = [
                'label' =>  $course_list[$course_id],
                'url' => backpack_url('payment/create?course=' . $course_id . '&student=' . $this->id),
            ];
        }
        $data['list'] = $list;
        $data['label'] = trans('account.store_payment');
        $data['show_icon'] = false;

        return view('vendor.backpack.crud.buttons.multilevel_dropdown', $data);
    }
}
