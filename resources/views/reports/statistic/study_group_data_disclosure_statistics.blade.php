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
            {{ empty(Setting::get('study_group_data_disclosure_statistics.title')) ? __('reports.study_group_data_disclosure_statistics') : Setting::get('study_group_data_disclosure_statistics.title') }}
        </h3>



        {!! Setting::get('study_group_data_disclosure_statistics.pre') !!}



        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>الصف</th>
                    <th>المستوى</th>
                    <th>المدرس</th>
                    <th>الغرفة</th>
                    <th>المكان</th>
                    <th>تاريخ البدء</th>
                    <th>الايام</th>
                    <th>تاريخ الإنتهاء</th>
                    <th>عدد الطلاب</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($classRooms as $key => $classRoom)
                    @foreach ($classRoom->students as $student)
                        @foreach ($classRoom->curriculums as $curriculum_id => $curriculum)
                            <tr>
                                <td>
                                    {{ $classRoom->long_name[$curriculum_id] ?? '' }}
                                </td>

                                <td> {{ $curriculum['short_name'] ?? '' }} </td>
                                <td> {{ $curriculum['teacher_name'] ?? '' }} </td>
                                <td>{{ $classRoom->class_room_number }}</td>
                                <td>{{ $classRoom->class_room_name }}</td>
                                <td>{{ $course->start_date->format('d-m-Y') }}</td>
                                <td>
                                    @isset($curriculum['days'])
                                        @foreach ($curriculum['days'] as $day)
                                            {{ $weekDays[$day] ?? '' }} {{ $curriculum['attend_table'][$day] ?? '' }} -
                                        @endforeach
                                    @endisset

                                </td>
                                <td>{{ $course->end_date->format('d-m-Y') }}</td>

                                <td>

                                    {{ $classRoom->students->count() }}

                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                @endforeach

            </tbody>
        </table>






        {!! Setting::get('study_group_data_disclosure_statistics.pro') !!}

    </div>
    <div class="new-page"></div>
@endsection
