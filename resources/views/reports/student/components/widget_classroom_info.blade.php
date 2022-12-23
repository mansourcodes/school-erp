<h2>{{ trans('account.payments') }}</h2>
@foreach ($payments as $payment)
    <table class="table table-striped table-bordered bg-gray">
        <tbody>
            <tr>
                <td>{{ $payment->id }}</td>
                <td> {{ trans('account.amount') }}: {{ $payment->amount }} </td>
                <td>{{ $payment->type }}</td>
            </tr>
        </tbody>
    </table>
@endforeach
@if (count($payments) == 0)
    <h4> ----- N/A ----- </h4>
@endif

<h2>{{ trans('classroom.classrooms') }}</h2>
@foreach ($classRoom->curriculums as $curriculum)
    <table class="table table-striped table-bordered">
        <tbody>
            <tr>
                <td>الصف</td>
                <td>{{ $classRoom->long_name }}</td>
            </tr>
            <tr>
                <td>رقم الصف</td>
                <td>{{ $classRoom->class_room_number }} {{ $classRoom->class_room_name }}</td>
            </tr>
            <tr>
                <td>مكان الدراسة</td>
                <td>{{ $classRoom->class_room_name }}</td>
            </tr>
            <tr>
                <td>يوم وتاريخ بدء الدراسة</td>
                <td>{{ $course->start_date->format('d-m-Y') }}</td>
            </tr>
            <tr>
                <td>يوم وتاريخ نهاية الدراسة</td>
                <td>{{ $course->end_date->format('d-m-Y') }}</td>
            </tr>
            <tr>
                <td>عدد ساعات المقررة</td>
                <td>{{ $course->duration }}</td>
            </tr>
            <tr>
                <td>وقت الدراسة</td>
                <td>
                    @foreach ($curriculum['days'] as $day)
                        {{ $weekDays[$day] ?? '' }} {{ $curriculum['attend_table'][$day] ?? '' }} <br>
                    @endforeach

                </td>
            </tr>
        </tbody>
    </table>
@endforeach
