<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Exemption tickets: a student does not pay for one course. Replaces `students.financial_support_status`
 * in the account reports (one ticket per student per course, checked by PaymentExemptionRequest because
 * soft-deleted tickets would block a unique index).
 */
class CreatePaymentExemptionsTable extends Migration
{
    public function up()
    {
        Schema::create('payment_exemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained();
            $table->foreignId('student_id')->constrained();
            $table->string('reason', 50);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['course_id', 'student_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_exemptions');
    }
}
