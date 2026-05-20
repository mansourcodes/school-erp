@extends('backend.layouts.' . $print)


@section('content')
    <div class="page">



        @if (Setting::get('print_header'))
            <img class="w-100" src="{{ URL::asset(Setting::get('print_header')) }}" />
        @endif

        <h1 class="title text-center">
            {{ Setting::get('student_attend_report.title') === '' ? __('reports.student_attend_report') : Setting::get('student_attend_report.title') }}
        </h1>




        {!! Setting::get('student_attend_report.pre') !!}




        <table class="table  ">
            <tr>
                <td>
                    <b>
                        Ø§Ù„ØµÙ:
                    </b>

                    {{ $classroom->class_room_name }}


                </td>
                <td>
                    <b>
                        Ø§Ù„Ù…Ø±Ø­Ù„Ø©:
                    </b>

                    {{ $classroom->course->academicPath->academic_path_name }}


                </td>
                <td>
                    <b>
                        Ø§Ù„Ø³Ù†Ø© Ø§Ù„Ø¯Ø±Ø§Ø³ÙŠØ©:
                    </b>


                    {{ $classroom->course->hijri_year }} Ù‡_

                    ({{ $classroom->course->course_year }} Ù…)

                </td>
                <td>
                    <b>
                        Ø§Ù„ÙØµÙ„:
                    </b>
                    {{ $classroom->course->semester }}

                </td>
            </tr>
        </table>


        <table class="table  table-ziped text-center">
            <tr>
                <th>#</th>
                <th>Ø§Ù„Ø§Ø³Ù…</th>
                <th>Ø§Ù„Ø­Ø¶ÙˆØ±</th>
                <th>Ø§Ù„ØºÙŠØ§Ø¨</th>
                <th>Ø§Ù„ØºÙŠØ§Ø¨ Ø¨Ø¹Ø°Ø±</th>
                <th>Ø§Ù„ØªØ£Ø®ÙŠØ±</th>
                <th>Ø§Ù„ØªØ£Ø®ÙŠØ± Ø¨Ø¹Ø°Ø±</th>
                <th>Ù…Ù„Ø§Ø­Ø¸Ø§Øª Ø§Ù„Ø¥Ø¯Ø§Ø±Ø©</th>

            </tr>
            @foreach ($classroom->students as $key => $student)
                <tr>
                    <td rowspan="2" class="align-middle">{{ $key + 1 }}</td>
                    <th rowspan="2" class="align-middle text-right">{{ $student->name }}</th>
                    <td>{{ $student_report[$student->id]['attend'] }}</td>
                    <td>{{ $student_report[$student->id]['absent'] }}</td>
                    <td>{{ $student_report[$student->id]['absentWithExcuse'] }}</td>
                    <td>{{ $student_report[$student->id]['late'] }}</td>
                    <td>{{ $student_report[$student->id]['lateWithExcuse'] }}</td>
                    <td rowspan="2" class="align-middle"></td>
                </tr>
                <tr>
                    <td>%{{ $student_report[$student->id]['attend_per'] }}</td>
                    <td>%{{ $student_report[$student->id]['absent_per'] }}</td>
                    <td>%{{ $student_report[$student->id]['absentWithExcuse_per'] }}</td>
                    <td>%{{ $student_report[$student->id]['late_per'] }}</td>
                    <td>%{{ $student_report[$student->id]['lateWithExcuse_per'] }}</td>
                </tr>
            @endforeach

        </table>

        <table class="table">
            <tr>
                <th class="align-middle">
                    Ø¹Ø¯Ø¯ Ø£ÙŠØ§Ù… Ø§Ù„Ø¯Ø±Ø§Ø³Ø©
                </th>
                <td class="align-middle">{{ $total_days }}</td>

            </tr>
        </table>



        {!! Setting::get('student_attend_report.pro') !!}

    </div>
@endsection
