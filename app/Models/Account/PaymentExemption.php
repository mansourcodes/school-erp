<?php

namespace App\Models\Account;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Exemption ticket: the student does not pay for the course. One live ticket per student per course.
 */
class PaymentExemption extends Model
{
    use CrudTrait;
    use SoftDeletes;

    protected $table = 'payment_exemptions';

    /** The first three match the old `students.financial_support_status` values. */
    public const REASONS = ['FINANCIAL_ISSUE', 'PARENTS_ISSUE', 'TEACHER_SONS', 'OTHER'];

    protected $fillable = [
        'course_id',
        'student_id',
        'reason',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'id' => 'integer',
        'course_id' => 'integer',
        'student_id' => 'integer',
        'created_by' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(\App\Models\Course::class);
    }

    public function student()
    {
        return $this->belongsTo(\App\Models\Student::class);
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /** reason key => translated label, for the CRUD select and filter. */
    public static function reasonOptions()
    {
        $options = [];
        foreach (self::REASONS as $reason) {
            $options[$reason] = __('account.exemption_reasons.' . $reason);
        }

        return $options;
    }

    public function getReasonLabelAttribute()
    {
        return __('account.exemption_reasons.' . $this->reason);
    }

    /** Whether the student also has a payment in the ticket's course (shown as a warning in the list). */
    public function hasPayment()
    {
        return Payment::where('course_id', $this->course_id)->where('student_id', $this->student_id)->exists();
    }
}
