@extends('layouts.' . $print)


@section('content')
    @foreach ($classRooms as $classRoom)
        @foreach ($classRoom->curriculums as $curriculum)
            <div class="page">

                @if (Setting::get('print_header'))
                    <img class="w-100" src="{{ URL::asset(Setting::get('print_header')) }}" />
                @endif

                <h1 class="title text-center">
                    {{ $course->long_name }}
                </h1>

                <h3 class="title text-center">
                    {{ empty(Setting::get('monthly_exams_template.title')) ? __('reports.monthly_exams_template') : Setting::get('monthly_exams_template.title') }}
                </h3>


                {!! Setting::get('monthly_exams_template.pre') !!}

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


                <table style="table-layout: auto;page-break-after: always;" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th width="1%">#</th>
                            <th width="20%">اسم الطالب</th>
                            <th>المجموع
                                (30 درجة)
                            </th>
                            <th>الوقفة التقويمية الأولى (10 درجات)
                                التاريخ : ..../.... /....
                            </th>
                            <th>الوقفة التقويمية الثانية (10 درجات)
                                التاريخ : ..../.... /....
                            </th>
                            <th>الوقفة التقويمية الثالثة (10 درجات)
                                التاريخ : ..../.... /....
                            </th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach ($classRoom->students as $key => $student)
                            <tr>
                                <td>{{ ++$key }}</td>
                                <td>
                                    {{ $student->student_name }}
                                </td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        @endforeach
                    </tbody>


                    </tbody>
                </table>





                {!! Setting::get('monthly_exams_template.pro') !!}

            </div>
            <div class="new-page"></div>
        @endforeach
    @endforeach
@endsection
