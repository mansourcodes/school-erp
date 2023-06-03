@extends('layouts.' . $print)


@section('content')
    <div class="page">

        @if (Setting::get('print_header'))
            <img class="w-100" src="{{ URL::asset(Setting::get('print_header')) }}" />
        @endif

        <h1 class="title text-center">
            {{ $course->long_name }}
        </h1>

        <h3 class="title text-center">
            {{ empty(Setting::get('student_detection_statistics_in_classes.title')) ? __('reports.student_detection_statistics_in_classes') : Setting::get('student_detection_statistics_in_classes.title') }}
        </h3>



        {!! Setting::get('student_detection_statistics_in_classes.pre') !!}



        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الإسم</th>
                    <th>الرقم الشخصي</th>
                    <th>المنطقة</th>
                    <th>النقال</th>
                    <th>الهاتف</th>
                    <th>الصف</th>
                    <th>الفترة</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $counter = 1;
                @endphp
                @foreach ($classRooms as $key => $classRoom)
                    @foreach ($classRoom->students as $student)
                        <tr>
                            <td>{{ $counter++ }}</td>
                            <td>{{ $student->student_name }}</td>
                            <td>{{ $student->cpr }}</td>
                            <td>{{ $student->address }}</td>
                            <td>{{ $student->mobile }}</td>
                            <td>{{ $student->mobile2 }}</td>
                            <td class="p-0">
                                @foreach ($classRoom->curriculums as $curriculum_id => $curriculum)
                                    <table class="table table-striped table-bordered m-0 w-100">
                                        <tbody>

                                            <tr>
                                                <td>{{ $classRoom->long_name[$curriculum_id] ?? '' }}</td>

                                                <td>
                                                    @isset($curriculum['attend_table'])
                                                        @php
                                                            echo current($curriculum['attend_table']);
                                                        @endphp
                                                    @endisset

                                                    {{-- @foreach ($curriculum['days'] as $day)
                                                        {{ $weekDays[$day] ?? '' }}
                                                        {{ $curriculum['attend_table'][$day] ?? '' }} -
                                                    @endforeach --}}

                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                @endforeach
                            </td>

                            <td>
                                @isset($curriculum['attend_table'])
                                    @php
                                        if (!empty($classRoom->curriculums)) {
                                            echo current(current($classRoom->curriculums)['attend_table']);
                                        }
                                    @endphp
                                @endisset

                            </td>
                        </tr>
                    @endforeach
                @endforeach

            </tbody>
        </table>






        {!! Setting::get('student_detection_statistics_in_classes.pro') !!}

    </div>
    <div class="new-page"></div>
@endsection
