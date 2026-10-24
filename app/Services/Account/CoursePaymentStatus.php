<?php

namespace App\Services\Account;

use App\Models\Account\Payment;
use App\Models\Account\PaymentExemption;
use App\Models\Course;
use Illuminate\Support\Collection;

/**
 * Payment status of every student enrolled in one course, shared by the account reports.
 *
 * Each student is in exactly one group, so students = paid + exempt + unpaid:
 *  - paid:   has at least one payment in the course (any type), exemption or not
 *  - exempt: has an exemption ticket and no payment
 *  - unpaid: neither
 * Paid students who also hold a ticket are flagged by paidAndExempt() (a subset of paid).
 */
class CoursePaymentStatus
{
    const PAID = 'paid';
    const EXEMPT = 'exempt';
    const UNPAID = 'unpaid';

    protected $course;

    /** student_id => Student, sorted by name */
    protected $students;

    /** student_id => classroom names in this course */
    protected $classRooms = [];

    /** student_id => Collection of Payment */
    protected $payments;

    /** student_id => PaymentExemption */
    protected $exemptions;

    public function __construct(Course $course)
    {
        $this->course = $course;

        $students = collect();
        foreach ($course->classRooms()->with('students')->get() as $classRoom) {
            foreach ($classRoom->students as $student) {
                $students[$student->id] = $student;
                $this->classRooms[$student->id][] = $classRoom->class_room_name;
            }
        }
        $this->students = $students->sortBy('name');

        $ids = $this->students->keys()->all();
        $this->payments = Payment::where('course_id', $course->id)->whereIn('student_id', $ids)
            ->orderBy('id')->get()->groupBy('student_id');
        $this->exemptions = PaymentExemption::with('creator')->where('course_id', $course->id)
            ->whereIn('student_id', $ids)->orderBy('id')->get()->keyBy('student_id');
    }

    public function statusOf($studentId)
    {
        if ($this->payments->has($studentId)) {
            return self::PAID;
        }

        return $this->exemptions->has($studentId) ? self::EXEMPT : self::UNPAID;
    }

    public function students()
    {
        return $this->students->values();
    }

    public function paid()
    {
        return $this->withStatus(self::PAID);
    }

    public function exempt()
    {
        return $this->withStatus(self::EXEMPT);
    }

    public function unpaid()
    {
        return $this->withStatus(self::UNPAID);
    }

    public function paidAndExempt()
    {
        return $this->paid()->filter(function ($student) {
            return $this->exemptions->has($student->id);
        })->values();
    }

    public function totals()
    {
        return [
            'students' => $this->students->count(),
            self::PAID => $this->paid()->count(),
            self::EXEMPT => $this->exempt()->count(),
            self::UNPAID => $this->unpaid()->count(),
            'paid_and_exempt' => $this->paidAndExempt()->count(),
        ];
    }

    /** @return Collection of Payment */
    public function paymentsOf($studentId)
    {
        return $this->payments->get($studentId, collect());
    }

    /** @return PaymentExemption|null */
    public function exemptionOf($studentId)
    {
        return $this->exemptions->get($studentId);
    }

    public function classRoomsOf($studentId)
    {
        return implode('، ', array_unique($this->classRooms[$studentId] ?? []));
    }

    protected function withStatus($status)
    {
        return $this->students->filter(function ($student) use ($status) {
            return $this->statusOf($student->id) === $status;
        })->values();
    }
}
