@extends('layouts.'.$print)


@section('content')
@foreach ($studentmarks as $studentmark)

<div class="page">

    <h2 class="text-center">{{$settings['student_edu_statement.title']['value'] ?? __('reports.student_edu_statement')}}</h2>




    <table class="table table-no-border ">
        <tr>
            <td>
                <b>  
                    نفيدكم علماً أنّ الطالب /
                </b>

                {{$studentmark->student->student_name}}
            </td>
            <td>
                <b>
                    الرقم الشخصي
                </b>

                {{$studentmark->student->cpr}}
            </td>
        </tr>
        <td>
            <b>  
                الفصل الدراسي /
            </b>

            {{$studentmark->course->academicPath->academic_path_name}}

            {{$studentmark->course->hijri_year}} هـ

            ({{$studentmark->course->course_year}} م)

            {{$studentmark->course->semester}}
            -
            {{$studentmark->course->academicPath->academic_path_type}}



        </td>
        <td>

        </td>
        </tr>
    </table>

    {!! $settings['student_edu_statement.pre']['value'] ?? '' !!}


    <table class="table">
        <tr>
            <th>
                المقرر
            </th>
            <th>
                الفرع
            </th>
            <th>
                المدرس
            </th>
            <th>
                الساعات
            </th>
            <th>
                الدرجة الكلية / 100
            </th>
            <th>
                الحالة
            </th>
        </tr>
        @foreach ($studentmark->marks as $subject_mark)
        <tr>
            <td>
                {{$curriculums[$subject_mark['curriculumـid']]->curriculumـname}}
                -
                {{$curriculums[$subject_mark['curriculumـid']]->bookـname}}
            </td>
            <td>
                {{$curriculums[$subject_mark['curriculumـid']]->curriculumCategory->categoryـname ?? '-'}}
            </td>

            <td>
                {{$curriculums[$subject_mark['curriculumـid']]->teacher_name}}

            </td>
            <td>
                {{$curriculums[$subject_mark['curriculumـid']]->weightـinـhours}}
            </td>


            <td>
                {{$subject_mark['total_mark']}}
            </td>
            <td>
                {{$subject_mark['final_grade']}}
            </td>
        </tr>
        @endforeach

    </table>

    {!! $settings['student_edu_statement.pro']['value'] ?? '' !!}

</div>
<div class="new-page"></div>


@endforeach
@endsection
