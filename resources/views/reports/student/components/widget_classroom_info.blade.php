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
    <table class="table table-striped table-bordered">
        <tbody>
            <tr>
                <td> ----- N/A ----- </td>
            </tr>
        </tbody>
    </table>
@endif

<h2>{{ trans('classroom.classrooms') }}</h2>
<table class="table table-striped table-bordered bg-info">
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
        @foreach ($classRoom->curriculums as $curriculum)
            <tr>
                <td>
                    {{ $curriculum['curriculumـname'] }} <br>
                    {{ $curriculum['teahcer_name'] ?? '' }}

                </td>
                <td>
                    @foreach ($curriculum['days'] as $day)
                        {{ $weekDays[$day] ?? '' }} {{ $curriculum['attend_table'][$day] ?? '' }} <br>
                    @endforeach

                </td>
            </tr>
        @endforeach

    </tbody>
</table>


<script>
    const target_{{ $wrapper_id }} = document.getElementById("{{ $wrapper_id }}").getElementsByClassName(
        "card-body")[0];
    const btn_{{ $wrapper_id }} = document.getElementById("{{ $wrapper_id }}").getElementsByClassName(
        "card-header")[0];
    btn_{{ $wrapper_id }}.onclick = function() {
        if (target_{{ $wrapper_id }}.style.display !== "block") {
            target_{{ $wrapper_id }}.style.display = "block";
        } else {
            target_{{ $wrapper_id }}.style.display = "none";
        }
    };
</script>
