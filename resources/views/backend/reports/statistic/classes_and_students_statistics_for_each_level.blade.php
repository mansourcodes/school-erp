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
            {{ empty(Setting::get('classes_and_students_statistics_for_each_level.title')) ? __('reports.classes_and_students_statistics_for_each_level') : Setting::get('classes_and_students_statistics_for_each_level.title') }}
        </h3>



        {!! Setting::get('classes_and_students_statistics_for_each_level.pre') !!}



        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Ø§Ù„Ù…Ø³ØªÙˆÙ‰</th>
                    <th>Ø¹Ø¯Ø¯ Ø§Ù„ØµÙÙˆÙ</th>
                    <th>Ø¹Ø¯Ø¯ Ø§Ù„Ø·Ù„Ø§Ø¨</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($curriculums_statistics as $key => $curriculum)
                    <tr>
                        <td>{{ $curriculum['curriculum_name'] }}</td>
                        <td>{{ $curriculum['count_class_rooms'] }}</td>
                        <td>{{ $curriculum['count_students'] }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td> --------------------- Ø§Ù„Ù…Ø¬Ù…ÙˆØ¹</td>
                    <td>{{ $total['count_class_rooms'] }}</td>
                    <td>{{ $total['count_students'] }}</td>
                </tr>
            </tbody>
        </table>






        {!! Setting::get('classes_and_students_statistics_for_each_level.pro') !!}

    </div>
@endsection
