@extends('layouts.'.$print)


@section('content')
@foreach ($studentmarks as $studentmark)

<div class="page">

    <h2 class="text-center">{{$settings['transcript.title']['value'] ?? __('reports.transcript')}}</h2>


    {!! $settings['transcript.pre']['value'] ?? '' !!}


    <table class="table table-no-border">
        <tr>
            <th>
                الاسم:
            </th>
            <td colspan="3">
                {{$studentmark->student->student_name}}
            </td>
            <th>
                البرنامج:
            </th>
            <td>
                {{$studentmark->course->academicPath->academic_path_type}}
            </td>
        </tr>
        <tr>
            <th>
                المرحلة:
            </th>
            <td>
                {{$studentmark->course->academicPath->academic_path_name}}
            </td>
            <th>
                السنة الدراسية:
            </th>
            <td>
                {{$studentmark->course->hijri_year}} هـ
            </td>
            <th>
                الفصل:
            </th>
            <td>
                {{$studentmark->course->semester}}
            </td>
        </tr>
    </table>


    <table class="table">
        <tr>
            <th>
                المادة الدراسية
            </th>
            <th>
                الكتاب
            </th>
            <th>
                الدرجة الكلية
            </th>
        </tr>
        @foreach ($studentmark->marks as $subject_mark)
        <tr>
            <td>
                {{$curriculums[$subject_mark->curriculumـid]->curriculumـname}}
            </td>
            <td>
                {{$curriculums[$subject_mark->curriculumـid]->bookـname}}
            </td>
            <td>
                {{$subject_mark->total_mark}}
            </td>
        </tr>
        @endforeach

    </table>

    {!! $settings['transcript.pro']['value'] ?? '' !!}

</div>
<div class="new-page"></div>


@endforeach
@endsection