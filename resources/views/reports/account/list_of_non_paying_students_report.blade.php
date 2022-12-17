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
            {{ empty(Setting::get('list_of_non_paying_students_report.title')) ? __('reports.list_of_non_paying_students_report') : Setting::get('list_of_non_paying_students_report.title') }}
        </h3>


        {!! Setting::get('list_of_non_paying_students_report.pre') !!}




        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>اسم الطالب</th>
                    <th>النقال</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($students as $key => $student)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $student->student_name }}</td>
                        <td>{{ $student->mobile }}</td>
                    </tr>
                @endforeach

            </tbody>
        </table>



        {!! Setting::get('list_of_non_paying_students_report.pro') !!}

    </div>
    <div class="new-page"></div>
@endsection
