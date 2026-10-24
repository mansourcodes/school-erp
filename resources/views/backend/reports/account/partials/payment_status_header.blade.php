{{-- Header of the payment status reports: print header, course, $report title / pre settings, course totals. --}}
@if (Setting::get('print_header'))
    <img class="w-100" src="{{ URL::asset(Setting::get('print_header')) }}" />
@endif

<h1 class="title text-center">
    {{ $course->long_name }}
</h1>

<h3 class="title text-center">
    {{ empty(Setting::get($report . '.title')) ? __('reports.' . $report) : Setting::get($report . '.title') }}
</h3>

<p class="text-center">
    {{ __('reports.payment_status.summary', [
        'students' => $totals['students'],
        'paid' => $totals['paid'],
        'exempt' => $totals['exempt'],
        'unpaid' => $totals['unpaid'],
    ]) }}
    @if ($totals['paid_and_exempt'] > 0)
        <br>{{ __('reports.payment_status.paid_and_exempt_note', ['count' => $totals['paid_and_exempt']]) }}
    @endif
</p>

{!! Setting::get($report . '.pre') !!}
