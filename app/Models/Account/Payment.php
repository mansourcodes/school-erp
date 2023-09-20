<?php

namespace App\Models\Account;

use App\Http\Controllers\Report\PaymentController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class Payment extends Model
{
    use CrudTrait;
    use HasFactory, SoftDeletes;

    protected $table = 'payments';
    protected $guarded = ['id'];


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'course_id',
        'student_id',
        'amount',
        'source',
        'type',
        'meta',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'course_id' => 'integer',
        'student_id' => 'integer',
        'amount' => 'double',
    ];


    protected $appends = [
        'class_room',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function course()
    {
        return $this->belongsTo(\App\Models\Course::class);
    }

    public function student()
    {
        return $this->belongsTo(\App\Models\Student::class);
    }

    public function getClassRoomAttribute()
    {
        return \App\Models\ClassRoom::where('course_id', $this->course_id)->whereDoesntHave('students', function ($q) {
            $q->where('student_id', $this->student_id);
        })->get();
    }


    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */


    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */


    public function getPrintButton($crud = false)
    {
        $data['crud'] = $crud;
        $data['url'] = backpack_url('paymentReports' . '?view=' . 'paymentPrint' . '&payment_id=' . $this->id);
        $data['label'] = trans('crud.export.print');

        return view('vendor.backpack.crud.buttons.custom_button', $data);
    }
}
