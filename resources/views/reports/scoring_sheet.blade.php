@extends('layouts.'.$print)


@section('content')

<style>
    @media print {
        @page {
            size: landscape
        }
    }
</style>

@foreach ($classroom->course->academicPath->curricula as $curriculum)

<div class="page">

    <h2 class="text-center">{{$settings['scoring_sheet.title']['value'] ?? __('reports.scoring_sheet')}}</h2>


    {!! $settings['scoring_sheet.pre']['value'] ?? '' !!}




    <table class="table table-no-border">
        <tr>
            <th>
                {{__('curriculum.curriculum')}}:
            </th>
            <td colspan="3">
                {{$curriculum->curriculum_name}}
            </td>
        </tr>
        <tr>
            <th>
                {{__('course.course')}}:

            </th>
            <td>
                {{$classroom->course->academicPath->academic_path_name}}
            </td>
            <th>
                السنة الدراسية:
            </th>
            <td>
                {{$classroom->course->hijri_year}} ه_
            </td>
            <th>
                الفصل:
            </th>
            <td>
                {{$classroom->course->semester}}
            </td>
        </tr>
    </table>


    <table class="table table-ziped text-center ">
        <tr>
            <th rowspan="2" class="align-middle">
                #
            </th>
            <th rowspan="2" class="align-middle">
                اسم الطالب
            </th>
            @if ($curriculum->marks_labels)
            @foreach (array_reverse($curriculum->marks_labels) as $section_key => $section)
            @if ($section)
            @foreach ($section as $key => $mark_design)
            <th>
                {{$mark_design->label}}
            </th>
            @endforeach
            @endif
            @endforeach
            @endif

            <th>
                المجموع
            </th>
        </tr>
        <tr>
            @if ($curriculum->marks_labels)
            @foreach (array_reverse($curriculum->marks_labels) as $section_key => $section)
            @if ($section)
            @foreach ($section as $key => $mark_design)
            <th>
                {{$mark_design->mark}}
            </th>
            @endforeach
            @endif
            @endforeach
            @endif

            <th>
                100
            </th>
        </tr>
        @foreach ($classroom->students as $key => $student)
        <tr>
            <td>
                {{$key+1}}
            </td>
            <td class=" text-right">
                {{$student->student_name}}
            </td>
            @if ($curriculum->marks_labels)
            @foreach (array_reverse($curriculum->marks_labels) as $section_key => $section)
            @if ($section)
            @foreach ($section as $key => $mark_design)
            <td>
                @php
                if(isset($marks[$curriculum->id][$student->id]->{$section_key}[$key]->mark)){
                echo $marks[$curriculum->id][$student->id]->{$section_key}[$key]->mark;
                }
                @endphp
            </td>
            @endforeach
            @endif
            @endforeach
            @endif
            <td>
                {{@$marks[$curriculum->id][$student->id]->total_mark}}

            </td>
        </tr>
        @endforeach

    </table>

    {!! $settings['scoring_sheet.pro']['value'] ?? '' !!}

</div>
<div class="new-page"></div>


@endforeach
@endsection