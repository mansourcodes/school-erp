<?php

namespace App\Models;

use App\Models\Account\Payment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Student extends Authenticatable
{
    use \Backpack\CRUD\app\Models\Traits\CrudTrait;
    use HasFactory, SoftDeletes, Notifiable;
    use HasRoles;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'cpr',
        'password',
        'email',
        'mobile',
        'mobile2',
        'dob',
        'address',
        'live_in_state',
        'relationship_state',
        'family_members',
        'family_depends',
        'degree',
        'hawza_history',
        'hawza_history_details',
        'health_history',
        'health_history_details',
        'financial_state',
        'financial_details',
        'student_notes',
        'registration_at',
        'financial_support_status',
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
        'id' => 'integer',
        'cpr' => 'integer',
        'mobile' => 'integer',
        'mobile2' => 'integer',
        'dob' => 'date',
        'hawza_history' => 'boolean',
        'health_history' => 'boolean',
        'registration_at' => 'date',
        'email_verified_at' => 'datetime',
    ];


    protected $appends = [
        'long_name',
        'student_id',
        'courses',
        'age',
    ];


    // - 
    public const FINANCIAL_SUPPORT_STATUS = [
        "UNKNOWN" => 'student.financial_support_status_options.UNKNOWN',
        "FINANCIAL_ISSUE" => 'student.financial_support_status_options.FINANCIAL_ISSUE',
        "PARENTS_ISSUE" => 'student.financial_support_status_options.PARENTS_ISSUE',
        "TEACHER_SONS" => 'student.financial_support_status_options.TEACHER_SONS',
    ];

    public static function FinancialSupportStatusArray()
    {
        $array = [];
        foreach (Student::FINANCIAL_SUPPORT_STATUS as $key => $value) {
            $array[$key] = __($value);
        }
        return $array;
    }

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

        return "[" . $this->cpr . "] " . $this->name . " [" . $this->mobile . $mobile2 . "]";
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
        try {


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
        } catch (\Throwable $th) {
            //throw $th;
            return 'error';
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
                    [
                        'label' => trans('reports.transcript'),
                        'url' => backpack_url('studentReports?view=transcript&course=' . $classRoom->course->id . '&student=' . $this->id . '&print=redirect'),
                    ],
                    [
                        'label' => trans('reports.certificate'),
                        'url' => backpack_url('studentReports?view=certificate&course=' . $classRoom->course->id . '&student=' . $this->id . '&print=redirect'),
                    ],
                    [
                        'label' => trans('reports.student_edu_statement'),
                        'url' => backpack_url('studentReports?view=student_edu_statement&course=' . $classRoom->course->id . '&student=' . $this->id . '&print=redirect'),
                    ],
                    [
                        'label' => trans('reports.student_courses_transcript'),
                        'url' => backpack_url('studentReports?view=student_courses_transcript&course=' . $classRoom->course->id . '&student=' . $this->id . '&print=redirect'),
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
