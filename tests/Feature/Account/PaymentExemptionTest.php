<?php

namespace Tests\Feature\Account;

use App\Models\Account\Payment;
use App\Models\Account\PaymentExemption;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\Student;
use App\Models\User;
use App\Services\Account\CoursePaymentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Exemption tickets, the paid / exempt / unpaid split and the account reports built on it. */
class PaymentExemptionTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected $course;

    /** @var \Illuminate\Support\Collection */
    protected $students;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        $this->admin->assignRole(Role::findOrCreate('Superadmin', 'web'));

        // 5 students over two classrooms; one sits in both and must count once.
        $this->course = Course::factory()->create(['is_active' => false]);
        [$a, $b] = ClassRoom::factory()->count(2)->create(['course_id' => $this->course->id]);
        $this->students = Student::factory()->count(5)->create();
        $a->students()->attach($this->students->take(3)->pluck('id'));
        $b->students()->attach($this->students->slice(2)->pluck('id'));
    }

    protected function pay(Student $student, $type = 'FULL')
    {
        return Payment::create(['course_id' => $this->course->id, 'student_id' => $student->id,
            'amount' => 25, 'source' => 'CASH', 'type' => $type]);
    }

    protected function exempt(Student $student, $reason = 'FINANCIAL_ISSUE')
    {
        return PaymentExemption::create(['course_id' => $this->course->id, 'student_id' => $student->id,
            'reason' => $reason]);
    }

    public function test_every_student_is_paid_exempt_or_unpaid_once()
    {
        [$paid, $partly, $exempt, $both, $none] = $this->students->all();
        $this->pay($paid);
        $this->pay($partly, 'PART');
        $this->exempt($exempt);
        $this->pay($both, 'OFFER');
        $this->exempt($both, 'TEACHER_SONS');

        $status = new CoursePaymentStatus($this->course);

        $this->assertSame(['students' => 5, 'paid' => 3, 'exempt' => 1, 'unpaid' => 1, 'paid_and_exempt' => 1],
            $status->totals());
        $this->assertEqualsCanonicalizing([$paid->id, $partly->id, $both->id], $status->paid()->pluck('id')->all());
        $this->assertSame([$exempt->id], $status->exempt()->pluck('id')->all());
        $this->assertSame([$none->id], $status->unpaid()->pluck('id')->all());
        $this->assertSame([$both->id], $status->paidAndExempt()->pluck('id')->all());
    }

    public function test_the_support_status_on_the_student_no_longer_counts()
    {
        $this->students[0]->update(['financial_support_status' => 'FINANCIAL_ISSUE']);

        $status = new CoursePaymentStatus($this->course);

        $this->assertSame(5, $status->unpaid()->count());
    }

    public function test_deleted_tickets_and_payments_of_other_courses_do_not_count()
    {
        $this->exempt($this->students[0])->delete();
        Payment::create(['course_id' => Course::factory()->create()->id, 'student_id' => $this->students[1]->id,
            'amount' => 25, 'source' => 'CASH', 'type' => 'FULL']);

        $this->assertSame(5, (new CoursePaymentStatus($this->course))->unpaid()->count());
    }

    public function test_each_report_lists_its_group()
    {
        [$paid, , $exempt, $both] = $this->students->all();
        $this->pay($paid);
        $this->exempt($exempt);
        $this->pay($both);
        $this->exempt($both);

        $reports = [
            'paidStudentsReport' => [$paid, $both],
            'exemptStudentsReport' => [$exempt],
            'listOfNonPayingStudentsReport' => [$this->students[1], $this->students[4]],
            'paidAndExemptStudentsReport' => [$both],
        ];

        foreach ($reports as $view => $listed) {
            $response = $this->actingAs($this->admin, 'backpack')
                ->get('/admin/accountReports?view=' . $view . '&course=' . $this->course->id)
                ->assertOk()
                ->assertSee('طلبة الدورة 5 = الدافعون 2 + المعفيون من الدفع 1 + غير الدافعين وغير المعفيين 2');

            foreach ($this->students as $student) {
                in_array($student, $listed)
                    ? $response->assertSee($student->name)
                    : $response->assertDontSee($student->name);
            }
        }
    }

    public function test_the_removed_reports_are_gone_from_the_course_menu()
    {
        $labels = collect(getReportClassFunctions(\App\Http\Controllers\Report\AccountController::class, 'accountReports', 1))
            ->pluck('label')->all();

        $this->assertSame([
            'كشف الارصدة',
            'قائمة الطلبة الدافعين',
            'كشف الطلبة المعفيين من الدفع',
            'قائمة الطلبة غير الدافعين وغير المعفيين',
            'كشف الطلبة المعفيين الذين قاموا بالدفع',
        ], $labels);
    }

    public function test_the_admin_opens_a_ticket_once_per_student_and_course()
    {
        $data = ['student_id' => $this->students[0]->id, 'course_id' => $this->course->id,
            'reason' => 'PARENTS_ISSUE', 'notes' => 'كفالة'];

        $this->actingAs($this->admin, 'backpack')->post('/admin/paymentexemption', $data)->assertRedirect();
        $ticket = PaymentExemption::firstOrFail();
        $this->assertSame($this->admin->id, $ticket->created_by);
        $this->assertSame('PARENTS_ISSUE', $ticket->reason);

        $this->actingAs($this->admin, 'backpack')->post('/admin/paymentexemption', $data)
            ->assertSessionHasErrors('student_id');
        $this->assertSame(1, PaymentExemption::count());

        // A deleted ticket does not block a new one.
        $ticket->delete();
        $this->actingAs($this->admin, 'backpack')->post('/admin/paymentexemption', $data)->assertSessionHasNoErrors();
        $this->assertSame(1, PaymentExemption::count());
    }

    public function test_an_unknown_reason_is_rejected()
    {
        $this->actingAs($this->admin, 'backpack')->post('/admin/paymentexemption', [
            'student_id' => $this->students[0]->id, 'course_id' => $this->course->id, 'reason' => 'UNKNOWN',
        ])->assertSessionHasErrors('reason');
    }

    public function test_the_admin_lists_tickets()
    {
        $this->pay($this->students[0]);
        $this->exempt($this->students[0]);

        // One request per test: Backpack's CrudPanel is a singleton, a second list request adds the filters twice.
        $rows = $this->actingAs($this->admin, 'backpack')
            ->postJson('/admin/paymentexemption/search', [], ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertJsonPath('recordsTotal', 1)->json('data');
        $this->assertStringContainsString('نعم', $rows[0][4]); // also paid
    }

    public function test_the_list_page_opens()
    {
        $this->actingAs($this->admin, 'backpack')->get('/admin/paymentexemption')->assertOk();
    }

    public function test_the_create_form_opens()
    {
        $this->actingAs($this->admin, 'backpack')->get('/admin/paymentexemption/create')->assertOk();
    }

    public function test_the_migration_opens_tickets_in_inactive_courses_for_real_support_reasons()
    {
        $active = Course::factory()->create(['is_active' => true]);
        ClassRoom::factory()->create(['course_id' => $active->id])->students()->attach($this->students->pluck('id'));

        $this->students[0]->update(['financial_support_status' => 'FINANCIAL_ISSUE']);
        $this->students[1]->update(['financial_support_status' => 'TEACHER_SONS']);
        $this->students[2]->update(['financial_support_status' => 'UNKNOWN']); // means "no support"
        $this->students[3]->update(['financial_support_status' => 'PARENTS_ISSUE']);
        $this->exempt($this->students[3], 'OTHER'); // already has a ticket

        $migration = new \CreatePaymentExemptionsFromFinancialSupportStatus(); // loaded by RefreshDatabase
        $migration->up();
        $migration->up(); // re-running adds nothing

        $this->assertSame(0, PaymentExemption::where('course_id', $active->id)->count());
        $this->assertEquals([
            $this->students[0]->id => 'FINANCIAL_ISSUE',
            $this->students[1]->id => 'TEACHER_SONS',
            $this->students[3]->id => 'OTHER',
        ], PaymentExemption::where('course_id', $this->course->id)->pluck('reason', 'student_id')->all());

        $migration->down();
        $this->assertSame(1, PaymentExemption::count()); // only the hand-made ticket is left
    }
}
