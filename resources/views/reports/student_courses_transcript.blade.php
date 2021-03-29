@extends('layouts.'.$print)


@section('content')
@foreach ($course_id as $id => $student_data)

<div class="page">

    <h2 class="text-center">{{$settings['student_courses_transcript.title']['value'] ?? __('reports.student_courses_transcript')}}</h2>




    <table class="table table-no-border ">
        <tr>
            <td>
                <b>  
                    نفيدكم علماً أنّ الطالب /
                </b>

                {{$student_data['studentmarks']->student->student_name}}
            </td>
            <td>
                <b>
                    الرقم الشخصي
                </b>

                {{$student_data['studentmarks']->student->cpr}}
            </td>
        </tr>
        <tr>
            <td>
                <b>  
                    الفصل الدراسي /
                </b>

                {{$student_data['studentmarks']->course->academicPath->academic_path_name}}

                {{$student_data['studentmarks']->course->hijri_year}} هـ

                ({{$student_data['studentmarks']->course->course_year}} م)

                {{$student_data['studentmarks']->course->semester}}
                -
                {{$student_data['studentmarks']->course->academicPath->academic_path_type}}



            </td>
            <td>

            </td>
        </tr>
    </table>

    {!! $settings['student_courses_transcript.pre']['value'] ?? '' !!}


    <table class="table">
        <tr>
            <th scope="row"></th>

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
                ...
            </th>
            <th>
                الدرجة الكلية / 100
            </th>
            <th>
                الحالة
            </th>
        </tr>
        <?php $counter = 0; ?>
        @foreach ($student_data['studentmarks']->marks as $subject_mark)
        <tr>
            <th scope="row">{{ ++$counter }}</th>

            <td>
                {{$student_data['curriculums'][$subject_mark['curriculumـid']]->curriculumـname}}
                -
                {{$student_data['curriculums'][$subject_mark['curriculumـid']]->bookـname}}
            </td>
            <td>
                {{$student_data['curriculums'][$subject_mark['curriculumـid']]->curriculumCategory->categoryـname ?? '-'}}
            </td>

            <td>
                {{$student_data['curriculums'][$subject_mark['curriculumـid']]->teacher_name}}

            </td>
            <td>
                {{$student_data['curriculums'][$subject_mark['curriculumـid']]->weightـinـhours}}
            </td>


            <td>
                {{$subject_mark['finalexam_mark_details'][0]['mark']}}
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

    {!! $settings['student_courses_transcript.pro']['value'] ?? '' !!}

</div>
<div class="new-page"></div>



@endforeach
@endsection