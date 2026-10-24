@extends('backend.layouts.' . $print)


@section('content')
    <div class="page">

        @include('backend.reports.account.partials.payment_status_header', ['report' => 'exempt_students_report'])

        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    @include('backend.reports.account.partials.student_head_cells')
                    <th>{{ __('reports.payment_status.exemption_ticket') }}</th>
                    <th>{{ __('reports.payment_status.exemption_reason') }}</th>
                    <th>{{ __('reports.payment_status.notes') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($students as $key => $student)
                    @php($exemption = $status->exemptionOf($student->id))
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        @include('backend.reports.account.partials.student_cells')
                        <td>#{{ $exemption->id }}</td>
                        <td>{{ $exemption->reason_label }}</td>
                        <td>{{ $exemption->notes }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {!! Setting::get('exempt_students_report.pro') !!}

    </div>
@endsection
