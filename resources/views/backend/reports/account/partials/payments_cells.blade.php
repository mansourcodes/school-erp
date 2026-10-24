{{-- The student's payments in the course (type: amount) and their total. --}}
<td>
    @foreach ($status->paymentsOf($student->id) as $payment)
        {{ $payment->type }}: {{ $payment->amount }}<br>
    @endforeach
</td>
<td>{{ $status->paymentsOf($student->id)->sum('amount') }}</td>
