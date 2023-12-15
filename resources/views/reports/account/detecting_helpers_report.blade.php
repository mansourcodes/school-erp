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
            {{ empty(Setting::get('detecting_helpers_report.title')) ? __('reports.detecting_helpers_report') : Setting::get('detecting_helpers_report.title') }}
        </h3>


        {!! Setting::get('detecting_helpers_report.pre') !!}


        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th># </th>
                    <th>رقم الطالب </th>
                    <th>الإسم</th>
                    <th>الرقم الشخصي</th>
                    <th>النقال</th>
                    <th>المنطقة</th>
                    <th>الصف</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payments as $key => $payment)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $payment->student->student_id }}</td>
                        <td>{{ $payment->student->name }}</td>
                        <td>{{ $payment->student->cpr }}</td>
                        <td>{{ $payment->student->mobile }}</td>
                        <td>{{ $payment->student->address }}</td>
                        <td>{{ $payment->classRoom->implode('class_room_name', ', ') }} </td>
                    </tr>
                @endforeach

            </tbody>
        </table>






        {!! Setting::get('detecting_helpers_report.pro') !!}

    </div>
    <div class="new-page"></div>
@endsection
