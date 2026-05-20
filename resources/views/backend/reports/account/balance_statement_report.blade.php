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
            {{ empty(Setting::get('balance_statement_report.title')) ? __('reports.balance_statement_report') : Setting::get('balance_statement_report.title') }}
        </h3>

        <p class=" text-center">Ù‡Ø°Ø§ Ø§Ù„ØªÙ‚Ø±ÙŠØ± ÙŠØ¸Ù‡Ø± Ø§Ù„Ø·Ù„Ø§Ø¨ Ø§Ù„Ø¯Ø§ÙØ¹ÙŠÙ† ÙˆØ§Ù„ØºÙŠØ± Ù…ÙØ±ÙˆØ²ÙŠÙ† Ø¨Ø§Ù„Ø¥Ø¶Ø§ÙØ© Ù„Ù„Ù…ÙØ±ÙˆØ²ÙŠÙ†</p>


        {!! Setting::get('balance_statement_report.pre') !!}


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
                    <th>Ø±Ù‚Ù… Ø§Ù„Ø±ØµÙŠØ¯</th>
                    <th>Ø§Ù„ØªØ¯Ù‚ÙŠÙ‚ </th>
                    <th>Ø§Ù„Ø±Ù‚Ù… </th>
                    <th>Ø§Ù„Ø¥Ø³Ù…</th>
                    <th>Ø§Ù„Ø¯ÙˆØ±Ø©</th>
                    <th>Ø§Ù„Ù†ÙˆØ¹</th>
                    <th>Ø§Ù„Ù…Ø¨Ù„Øº</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payments as $key => $payment)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $payment->id }}</td>
                        <td></td>
                        <td>{{ $payment->student->student_id }}</td>
                        <td>{{ $payment->student->name }}</td>
                        <td> {{ $course->long_name }} </td>
                        <td>{{ $payment->type }}</td>
                        <td>{{ $payment->amount }}</td>
                    </tr>
                @endforeach

            </tbody>
        </table>






        {!! Setting::get('balance_statement_report.pro') !!}

    </div>
@endsection
