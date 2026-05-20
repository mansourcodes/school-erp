@extends('backend.layouts.' . $print)


@section('content')
    @foreach ($classroom->students as $student)
        <div class="page">

            @if (Setting::get('print_header'))
                <img class="w-100" src="{{ URL::asset(Setting::get('print_header')) }}" />
            @endif

            <h1 class="title text-center">
                {{ Setting::get('student_attend.title') === '' ? __('reports.student_attend') : Setting::get('student_attend.title') }}
            </h1>




            {!! Setting::get('student_attend.pre') !!}


            <table class="table  ">
                <tr>
                    <td colspan="2">
                        <b>
                            Ø§Ù„Ø§Ø³Ù…:
                        </b>

                        {{ $student->name }}
                    </td>
                    <td>
                        <b>
                            Ø§Ù„Ø¨Ø±Ù†Ø§Ù…Ø¬:
                        </b>

                        {{ $classroom->course->academicPath->academic_path_type }}
                    </td>
                </tr>
                <tr>
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

            <br>

            <table class="table text-center ">
                <tr>
                    <th rowspan="2 " class="align-middle">
                        Ø¹Ø¯Ø¯ Ø£ÙŠØ§Ù… Ø§Ù„Ø¯Ø±Ø§Ø³Ø©
                    </th>
                    <th rowspan="2" class="align-middle">
                        Ø§Ù„Ø­Ø¶ÙˆØ±
                    </th>
                    <th colspan="2">
                        Ø¹Ø¯Ø¯ Ø£ÙŠØ§Ù… Ø§Ù„ØºÙŠØ§Ø¨
                    </th>
                    <th colspan="2">
                        Ø¹Ø¯Ø¯ Ø£ÙŠØ§Ù… Ø§Ù„ØªØ£Ø®ÙŠØ±
                    </th>
                </tr>
                <tr>
                    <th>
                        Ø¨Ø¹Ø°Ø±
                    </th>
                    <th>
                        Ù…Ù† Ø¯ÙˆÙ† Ø¹Ø°Ø±
                    </th>
                    <th>
                        Ø¨Ø¹Ø°Ø±
                    </th>
                    <th>
                        Ù…Ù† Ø¯ÙˆÙ† Ø¹Ø°Ø±
                    </th>
                </tr>
                <tr>
                    <td rowspan="2" class="align-middle">{{ $total_days }}</td>
                    <td>{{ $student_report[$student->id]['attend'] }}</td>
                    <td>{{ $student_report[$student->id]['absent'] }}</td>
                    <td>{{ $student_report[$student->id]['absentWithExcuse'] }}</td>
                    <td>{{ $student_report[$student->id]['late'] }}</td>
                    <td>{{ $student_report[$student->id]['lateWithExcuse'] }}</td>
                </tr>
                <tr>
                    <td>%{{ $student_report[$student->id]['attend_per'] }}</td>
                    <td>%{{ $student_report[$student->id]['absent_per'] }}</td>
                    <td>%{{ $student_report[$student->id]['absentWithExcuse_per'] }}</td>
                    <td>%{{ $student_report[$student->id]['late_per'] }}</td>
                    <td>%{{ $student_report[$student->id]['lateWithExcuse_per'] }}</td>
                </tr>
            </table>



            {!! Setting::get('student_attend.pro') !!}

        </div>

        @if (!$loop->last)
            <div class="new-page"></div>
        @endif
    @endforeach
@endsection
