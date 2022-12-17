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
            {{ empty(Setting::get('list_of_assistance_students_who_participated_in_the_payment_report.title')) ? __('reports.list_of_assistance_students_who_participated_in_the_payment_report') : Setting::get('list_of_assistance_students_who_participated_in_the_payment_report.title') }}
        </h3>


        {!! Setting::get('list_of_assistance_students_who_participated_in_the_payment_report.pre') !!}




        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th># </th>
                    <th>رقم الطالب </th>
                    <th>الإسم</th>
                    <th>الرقم الشخصي</th>
                    <th>الرصيد المعفي</th>
                    <th>الرصيد الاخر</th>
                    <th>نوع الرصيد الاخر</th>
                    <th>مبلغ الرصيد الاخر</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payments_free as $key => $payment)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $payment->student->student_id }}</td>
                        <td>{{ $payment->student->student_name }}</td>
                        <td>{{ $payment->student->cpr }}</td>
                        <td>{{ $payment->id }}</td>
                        <td>{{ $payments_paid->where('student_id', $payment->student_id)->first()->id }}</td>
                        <td>{{ $payments_paid->where('student_id', $payment->student_id)->first()->type }}</td>
                        <td>{{ $payments_paid->where('student_id', $payment->student_id)->first()->amount }}</td>
                    </tr>
                @endforeach

            </tbody>
        </table>





        {!! Setting::get('list_of_assistance_students_who_participated_in_the_payment_report.pro') !!}

    </div>
    <div class="new-page"></div>
@endsection
