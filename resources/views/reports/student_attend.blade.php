@extends('layouts.'.$print)


@section('content')



@foreach ($classroom->students as $student)

<div class="page">

    <h2 class="text-center">{{$settings['student_attend.title']['value'] ?? __('reports.student_attend')}}</h2>


    {!! $settings['student_attend.pre']['value'] ?? '' !!}


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

    <br>

    <table class="table text-center ">
        <tr>
            <th rowspan="2">
                عدد أيام الدراسة
            </th>
            <th rowspan="2">
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
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <!-- TODO: fill the report -->
        </tr>
    </table>

    <br>


    <table class="table text-center ">
        <tr>
            <th>
                نسبة الحضور
            </th>
            <th>
                نسبة الغياب
            </th>
            <th>
                نسبة التأخير
            </th>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>

    {!! $settings['student_attend.pro']['value'] ?? '' !!}

</div>
<div class="new-page"></div>


@endforeach
@endsection
