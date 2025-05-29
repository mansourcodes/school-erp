@extends('layouts.' . $print)


@section('content')
    @foreach ($classRooms as $classRoom)
        @foreach ($classRoom->curriculums as $key => $curriculum)
            <div class="page">

                @if (Setting::get('print_header'))
                    <img class="w-100" src="{{ URL::asset(Setting::get('print_header')) }}" />
                @endif

                <h3 class="title text-center">
                    {{ $course->long_name }}
                </h3>

                <h3 class="title text-center">
                    {{ empty(Setting::get('classroom_list.title')) ? __('reports.classroom_list') : Setting::get('classroom_list.title') }}
                </h3>


                {!! Setting::get('classroom_list.pre') !!}

                <table class="table table-striped table-bordered">
                    <tbody>
                        <tr>
                            <td>
                                {{ $classRoom->long_name[$key] }}</td>
                        </tr>
                    </tbody>
                </table>

                <table class="table table-striped table-bordered">


                    <tbody>
                        <tr>
                            <td>اسم المدرس</td>
                            <td>{{ $curriculum['teacher_name'] ?? '' }}</td>
                        </tr>
                        <tr>
                            <td>رقم الصف</td>
                            <td>{{ $classRoom->class_room_number }} {{ $classRoom->class_room_name }}</td>
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
                            <td>عدد الساعات المقررة</td>
                            <td>{{ $course->duration }}</td>
                        </tr>
                        <tr>
                            <td>وقت الدراسة</td>
                            <td>
                                @isset($curriculum['days'])
                                    @foreach ($curriculum['days'] as $day)
                                        {{ $weekDays[$day] ?? '' }} {{ $curriculum['attend_table'][$day] ?? '' }} <br>
                                    @endforeach
                                @endisset

                            </td>
                        </tr>
                    </tbody>
                    <tbody>
                    </tbody>
                </table>



                {!! Setting::get('classroom_list.pro') !!}

            </div>

            @if (!$loop->parent->last)
                <div class="new-page"></div>
            @endif
        @endforeach
    @endforeach
@endsection
