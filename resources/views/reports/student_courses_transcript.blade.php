@extends('layouts.'.$print)


@section('content')
@foreach ($course_id as $id => $student_data)

<div class="page">

    @if(Setting::get('print_header'))
    <img class="w-100" src="{{URL::asset(Setting::get('print_header'))}}" />
    @endif

    <h1 class="title text-center">
        {{Setting::get('student_courses_transcript.title') === '' ? __('reports.student_courses_transcript') : Setting::get('student_courses_transcript.title') }}
    </h1>





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

                {{$student_data['studentmarks']->course->hijri_year}} ه_

                ({{$student_data['studentmarks']->course->course_year}} م)

                {{$student_data['studentmarks']->course->semester}}
                -
                {{$student_data['studentmarks']->course->academicPath->academic_path_type}}



            </td>
            <td>

            </td>
        </tr>
    </table>

    {!! Setting::get('student_courses_transcript.pre') !!}


    <table class="table">
        <tr>
            <th scope="row"></th>

            <th>
                المقرر
            </th>
            <th>
                {{__('curriculumcategory.curriculumcategory')}}
            </th>
            <th>
                المدرس
            </th>
            <th>
                الساعات
            </th>
            <th>
                مجموع الاعمال
            </th>
            <th>
                إختبار المنتصف
            </th>
            <th>
                الاختبار النهائي
            </th>
            <th>
                الحضور والانضباط
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
                {{$student_data['curriculums'][$subject_mark->curriculum_id]->curriculum_name}}
                -
                {{$student_data['curriculums'][$subject_mark->curriculum_id]->book_name}}
            </td>
            <td>
                {{$student_data['curriculums'][$subject_mark->curriculum_id]->curriculumCategory->category_name ?? '-'}}
            </td>

            <td>
                {{$student_data['curriculums'][$subject_mark->curriculum_id]->teacher_name}}

            </td>
            <td>
                {{$student_data['curriculums'][$subject_mark->curriculum_id]->weight_in_hours}}
            </td>


            <td>
                {{$subject_mark->class_mark_details[0]->mark ?? ''}}
            </td>

            <td>
                {{$subject_mark->midexam_marks_details[0]->mark ?? ''}}
            </td>

            <td>
                {{$subject_mark->finalexam_mark_details[0]->mark ?? ''}}
            </td>

            <td>
                {{$subject_mark->attend_mark_details[0]->mark ?? ''}}
            </td>

            <td>
                {{$subject_mark->total_mark}}
            </td>
            <td>
                {{$subject_mark->final_grade}}
            </td>
        </tr>
        @endforeach

    </table>

    {!! Setting::get('student_courses_transcript.pro') !!}

</div>
<div class="new-page"></div>



@endforeach
@endsection
