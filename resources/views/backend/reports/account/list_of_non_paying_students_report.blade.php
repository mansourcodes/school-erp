@extends('backend.layouts.' . $print)


@section('content')
    <div class="page">

        @include('backend.reports.account.partials.payment_status_header', ['report' => 'list_of_non_paying_students_report'])

        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    @include('backend.reports.account.partials.student_head_cells')
                </tr>
            </thead>
            <tbody>
                @foreach ($students as $key => $student)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        @include('backend.reports.account.partials.student_cells')
                    </tr>
                @endforeach
            </tbody>
        </table>

        {!! Setting::get('list_of_non_paying_students_report.pro') !!}

    </div>
@endsection
