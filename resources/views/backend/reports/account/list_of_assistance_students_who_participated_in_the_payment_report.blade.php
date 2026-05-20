@extends('backend.layouts.' . $print)


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
                    <th>Ø±Ù‚Ù… Ø§Ù„Ø·Ø§Ù„Ø¨ </th>
                    <th>Ø§Ù„Ø¥Ø³Ù…</th>
                    <th>Ø§Ù„Ù‡Ø§ØªÙ</th>
                    <th>Ø§Ù„Ø±ØµÙŠØ¯ id</th>
                    <th>Ù†ÙˆØ¹ Ø§Ù„Ø±ØµÙŠØ¯ </th>
                    <th>Ù…Ø¨Ù„Øº Ø§Ù„Ø±ØµÙŠØ¯ </th>
                    <th>Ø§Ù„Ø¯Ø¹Ù…</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payments_paid as $key => $payment)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $payment->student->student_id }}</td>
                        <td>{{ $payment->student->name }}</td>
                        <td>{{ $payment->student->mobile }}</td>
                        <td>{{ $payment->id }}</td>
                        <td>{{ $payment->type }}</td>
                        <td>{{ $payment->amount }}</td>
                        <td>{{ __('student.financial_support_status_options.' . $payment->student->financial_support_status) }}
                        </td>
                    </tr>
                @endforeach

            </tbody>
        </table>





        {!! Setting::get('list_of_assistance_students_who_participated_in_the_payment_report.pro') !!}

    </div>
@endsection
