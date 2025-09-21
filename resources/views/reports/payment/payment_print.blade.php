@extends('layouts.' . $print)


@section('content')
    <div class="page">

        @for ($i = 0; $i < 2; $i++)
            @include('reports.payment.payment_print_template', ['payment' => $payment])
        @endfor
    </div>
@endsection
