@extends('layouts.' . $print)


@section('content')
    <div class="page">

        @if (Setting::get('print_header'))
            <img class="w-100" src="{{ URL::asset(Setting::get('print_header')) }}" />
        @endif

        <h1 class="title text-center">
            {{ $course->long_name }}
        </h1>

        <h3 class="title text-center">
            {{ empty(Setting::get('class_average_score_statistics.blade.title')) ? __('reports.class_average_score_statistics.blade') : Setting::get('class_average_score_statistics.blade.title') }}
        </h3>

        <p class=" text-center">هذا التقرير يظهر الطلاب الدافعين والغير مفروزين بالإضافة للمفروزين</p>


        {!! Setting::get('class_average_score_statistics.blade.pre') !!}


        <p>
            Total funds: {{ $payments->count() }} <br>

            @foreach ($payment_filtered as $key => $payment_collection)
                @continue($payment_collection->count() == 0)
                Total type: {{ $key }} Total number: {{ $payment_collection->count() }} <br>
            @endforeach

            Total BD: {{ $payments->sum('amount') }} <br>
        </p>

        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>رقم الرصيد</th>
                    <th>التدقيق </th>
                    <th>الرقم </th>
                    <th>الإسم</th>
                    <th>الدورة</th>
                    <th>النوع</th>
                    <th>المبلغ</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payments as $key => $payment)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $payment->id }}</td>
                        <td></td>
                        <td>{{ $payment->student->student_id }}</td>
                        <td>{{ $payment->student->student_name }}</td>
                        <td> {{ $course->long_name }} </td>
                        <td>{{ $payment->type }}</td>
                        <td>{{ $payment->amount }}</td>
                    </tr>
                @endforeach

            </tbody>
        </table>






        {!! Setting::get('class_average_score_statistics.blade.pro') !!}

    </div>
    <div class="new-page"></div>
@endsection
