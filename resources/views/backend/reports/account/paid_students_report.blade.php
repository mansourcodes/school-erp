@extends('backend.layouts.' . $print)


@section('content')
    <div class="page">

        @include('backend.reports.account.partials.payment_status_header', ['report' => 'paid_students_report'])

        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    @include('backend.reports.account.partials.student_head_cells')
                    <th>{{ __('reports.payment_status.payments') }}</th>
                    <th>{{ __('reports.payment_status.total_amount') }}</th>
                    <th>{{ __('reports.payment_status.exemption_ticket') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($students as $key => $student)
                    @php($exemption = $status->exemptionOf($student->id))
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        @include('backend.reports.account.partials.student_cells')
                        @include('backend.reports.account.partials.payments_cells')
                        <td>{{ $exemption ? '#' . $exemption->id . ' ' . $exemption->reason_label : '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {!! Setting::get('paid_students_report.pro') !!}

    </div>
@endsection
