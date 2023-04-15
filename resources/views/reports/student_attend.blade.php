@extends('layouts.'.$print)


@section('content')



@foreach ($classroom->students as $student)

<div class="page">

    @if(Setting::get('print_header'))
    <img class="w-100" src="{{URL::asset(Setting::get('print_header'))}}" />
    @endif

    <h1 class="title text-center">
        {{Setting::get('student_attend.title') === '' ? __('reports.student_attend') : Setting::get('student_attend.title') }}
    </h1>




    {!! Setting::get('student_attend.pre') !!}


    <table class="table  ">
        <tr>
            <td colspan="2">
                <b>  
                    الاسم:
                </b>

                {{$student->student_name}}
            </td>
            <td>
                <b>
                    البرنامج:
                </b>

                {{$classroom->course->academicPath->academic_path_type}}
            </td>
        </tr>
        <tr>
            <td>
                <b>  
                    المرحلة:
                </b>

                {{$classroom->course->academicPath->academic_path_name}}


            </td>
            <td>
                <b>  
                    السنة الدراسية:
                </b>


                {{$classroom->course->hijri_year}} ه_

                ({{$classroom->course->course_year}} م)

            </td>
            <td>
                <b>  
                    الفصل:
                </b>
                {{$classroom->course->semester}}

            </td>
        </tr>
    </table>

    <br>

    <table class="table text-center ">
        <tr>
            <th rowspan="2 " class="align-middle">
                عدد أيام الدراسة
            </th>
            <th rowspan="2" class="align-middle">
                الحضور
            </th>
            <th colspan="2">
                عدد أيام الغياب
            </th>
            <th colspan="2">
                عدد أيام التأخير
            </th>
        </tr>
        <tr>
            <th>
                بعذر
            </th>
            <th>
                من دون عذر
            </th>
            <th>
                بعذر
            </th>
            <th>
                من دون عذر
            </th>
        </tr>
        <tr>
            <td rowspan="2" class="align-middle">{{$total_days}}</td>
            <td>{{$student_report[$student->id]['attend']}}</td>
            <td>{{$student_report[$student->id]['absent']}}</td>
            <td>{{$student_report[$student->id]['absentWithExcuse']}}</td>
            <td>{{$student_report[$student->id]['late']}}</td>
            <td>{{$student_report[$student->id]['lateWithExcuse']}}</td>
        </tr>
        <tr>
            <td>%{{$student_report[$student->id]['attend_per']}}</td>
            <td>%{{$student_report[$student->id]['absent_per']}}</td>
            <td>%{{$student_report[$student->id]['absentWithExcuse_per']}}</td>
            <td>%{{$student_report[$student->id]['late_per']}}</td>
            <td>%{{$student_report[$student->id]['lateWithExcuse_per']}}</td>
        </tr>
    </table>



    {!! Setting::get('student_attend.pro') !!}

</div>
<div class="new-page"></div>


@endforeach
@endsection
