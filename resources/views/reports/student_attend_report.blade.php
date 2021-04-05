@extends('layouts.'.$print)


@section('content')




<div class="page">



    @if(Setting::get('print_header'))
    <img class="w-100" src="{{URL::asset(Setting::get('print_header'))}}" />
    @endif

    <h1 class="title text-center">
        {{Setting::get('student_attend_report.title') === '' ? __('reports.student_attend_report') : Setting::get('student_attend_report.title') }}
    </h1>




    {!! Setting::get('student_attend_report.pre') !!}




    <table class="table  ">
        <tr>
            <td>
                <b>  
                    الصف:
                </b>

                {{$classroom->class_room_name}}


            </td>
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


                {{$classroom->course->hijri_year}} هـ

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


    <table class="table  table-ziped text-center">
        <tr>
            <th>#</th>
            <th>الاسم</th>
            <th>الحضور</th>
            <th>الغياب</th>
            <th>الغياب بعذر</th>
            <th>التأخير</th>
            <th>التأخير بعذر</th>
            <th>ملاحظات الإدارة</th>

        </tr>
        @foreach ($classroom->students as $key => $student)

        <tr>
            <td rowspan="2" class="align-middle">{{$key+1}}</td>
            <th rowspan="2" class="align-middle text-right">{{$student->student_name}}</th>
            <td>{{$student_report[$student->id]['attend']}}</td>
            <td>{{$student_report[$student->id]['absent']}}</td>
            <td>{{$student_report[$student->id]['absentWithExcuse']}}</td>
            <td>{{$student_report[$student->id]['late']}}</td>
            <td>{{$student_report[$student->id]['lateWithExcuse']}}</td>
            <td rowspan="2" class="align-middle"></td>
        </tr>
        <tr>
            <td>%{{$student_report[$student->id]['attend_per']}}</td>
            <td>%{{$student_report[$student->id]['absent_per']}}</td>
            <td>%{{$student_report[$student->id]['absentWithExcuse_per']}}</td>
            <td>%{{$student_report[$student->id]['late_per']}}</td>
            <td>%{{$student_report[$student->id]['lateWithExcuse_per']}}</td>
        </tr>
        @endforeach

    </table>

    <table class="table">
        <tr>
            <th class="align-middle">
                عدد أيام الدراسة
            </th>
            <td class="align-middle">{{$total_days}}</td>

        </tr>
    </table>



    {!! Setting::get('student_attend_report.pro') !!}

</div>
<div class="new-page"></div>


@endsection
