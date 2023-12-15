@extends('layouts.' . $print)


@section('content')
    @foreach ($studentmarks as $studentmark)
        <div class="page">

            @if (Setting::get('print_header'))
                <img class="w-100" src="{{ URL::asset(Setting::get('print_header')) }}" />
            @endif

            <h1 class="title text-center">
                {{ Setting::get('transcript.title') === '' ? __('reports.transcript') : Setting::get('transcript.title') }}
            </h1>

            {!! Setting::get('transcript.pre') !!}


            <table class="table table-no-border">
                <tr>
                    <th>
                        الاسم:
                    </th>
                    <td colspan="3">
                        {{ $studentmark->student->name }}
                    </td>
                    <th>
                        البرنامج:
                    </th>
                    <td>
                        {{ $studentmark->course->academicPath->academic_path_type }}
                    </td>
                </tr>
                <tr>
                    <th>
                        المرحلة:
                    </th>
                    <td>
                        {{ $studentmark->course->academicPath->academic_path_name }}
                    </td>
                    <th>
                        السنة الدراسية:
                    </th>
                    <td>
                        {{ $studentmark->course->hijri_year }} ه_
                    </td>
                    <th>
                        الفصل:
                    </th>
                    <td>
                        {{ $studentmark->course->semester }}
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
                            {{ $curriculums[$subject_mark['curriculum_id']]->curriculum_name }}
                        </td>
                        <td>
                            {{ $curriculums[$subject_mark['curriculum_id']]->book_name }}
                        </td>
                        <td>
                            {{ $subject_mark['total_mark'] }}
                        </td>
                    </tr>
                @endforeach

            </table>

            {!! Setting::get('transcript.pro') !!}

        </div>
        <div class="new-page"></div>
    @endforeach
@endsection
