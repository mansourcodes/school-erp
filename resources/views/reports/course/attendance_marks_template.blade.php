@extends('layouts.' . $print)


@section('content')
    @foreach ($classRooms as $classRoom)
        @foreach ($classRoom->curriculums as $curriculum)
            <div class="page">

                @if (Setting::get('print_header'))
                    <img class="w-100" src="{{ URL::asset(Setting::get('print_header')) }}" />
                @endif

                <h1 class="title text-center">
                    {{ empty(Setting::get('attendance_marks_template.title')) ? __('reports.attendance_marks_template') : Setting::get('attendance_marks_template.title') }}
                </h1>


                {!! Setting::get('attendance_marks_template.pre') !!}

                <table class="table table-striped table-bordered">
                    <tbody>
                        <tr>
                            <td>
                                {{ $curriculum['curriculumـname'] ?? '' }} -
                                {{ $curriculum['teacher_name'] ?? '' }} -
                                {{ $classRoom->long_name }}</td>
                        </tr>
                    </tbody>
                </table>

                <table class="table table-striped table-bordered" style="page-break-after: always;">
                    <thead>
                        <tr>
                            <th>اسم الطالب</th>
                            <th>الحفظ (5 درجات)</th>
                            <th>السلوك (5 درجات)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($classRoom->students as $student)
                            <tr>
                                <td>
                                    {{ $student->student_name }}
                                </td>
                                <td></td>
                                <td></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>




                {!! Setting::get('attendance_marks_template.pro') !!}

            </div>
            <div class="new-page"></div>
        @endforeach
    @endforeach
@endsection
