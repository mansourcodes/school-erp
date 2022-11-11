@extends('layouts.' . $print)


@section('content')
    @foreach ($classRooms as $classRoom)
        @foreach ($classRoom->students as $student)
            <div class="page">

                @if (Setting::get('print_header'))
                    <img class="w-100" src="{{ URL::asset(Setting::get('print_header')) }}" />
                @endif

                <h1 class="title text-center">
                    {{ Setting::get('student_tables.title') === '' ? __('reports.student_table') : Setting::get('student_tables.title') }}
                </h1>

                {!! Setting::get('student_tables.pre') !!}


                <table class="table table-striped table-bordered">


                    <tbody>
                        <tr>
                            <td width="200px">اسم الطالب</td>
                            <td>{{ $student->student_name }}</td>
                        </tr>
                        <tr>
                            <td>رقم الطالب</td>
                            <td>{{ $student->student_id }}</td>
                        </tr>
                        <tr>
                            <td>رقم الصف</td>
                            <td>{{ $classRoom->class_room_number }}</td>
                        </tr>
                        <tr>
                            <td>مكان الدراسة</td>
                            <td>{{ $classRoom->class_room_name }}</td>
                        </tr>
                        <tr>
                            <td>يوم وتاريخ بدء الدراسة</td>
                            <td>{{ $course->start_date->format('d-m-Y') }}</td>
                        </tr>
                        <tr>
                            <td>يوم وتاريخ نهاية الدراسة</td>
                            <td>{{ $course->end_date->format('d-m-Y') }}</td>
                        </tr>
                        <tr>
                            <td>عدد ساعات المقررة</td>
                            <td>{{ $course->duration }}</td>
                        </tr>
                    </tbody>
                </table>
                @foreach ($classRoom->curriculums as $curriculum)
                    <table class="table table-striped table-bordered">
                        <tbody>

                            <tr>
                                <td width="200px">المنهج</td>
                                <td>{{ $curriculum['curriculumـname'] ?? '' }}</td>
                            </tr>

                            <tr>
                                <td>اسم المدرس</td>
                                <td>{{ $curriculum['teacher_name'] ?? '' }}</td>
                            </tr>
                            {{-- <tr>
                                <td>مدة الحصة</td>
                                <td>ساعة</td>
                            </tr> --}}
                            <tr>
                                <td>وقت الدراسة</td>
                                <td>
                                    @foreach ($curriculum['days'] as $day)
                                        {{ $weekDays[$day] ?? '' }} {{ $curriculum['attend_table'][$day] ?? '' }} <br>
                                    @endforeach

                                </td>
                            </tr>
                        </tbody>
                        <tbody>
                        </tbody>
                    </table>
                @endforeach



                {!! Setting::get('student_tables.pro') !!}

            </div>
            <div class="new-page"></div>
        @endforeach
    @endforeach
@endsection
