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
            {{ empty(Setting::get('statistics_for_the_number_of_students_in_classes.title')) ? __('reports.statistics_for_the_number_of_students_in_classes') : Setting::get('statistics_for_the_number_of_students_in_classes.title') }}
        </h3>



        {!! Setting::get('statistics_for_the_number_of_students_in_classes.pre') !!}



        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>الصف</th>
                    <th>عدد الطلاب</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($classRooms as $key => $classRoom)
                    @foreach ($classRoom->students as $student)
                        @foreach ($classRoom->curriculums as $curriculum)
                            <tr>
                                <td>{{ $curriculum['teacher_name'] ?? '' }}
                                    - {{ $curriculum['curriculumـname'] ?? '' }}
                                    - {{ $classRoom->class_room_number }}
                                </td>

                                <td>

                                    {{ $classRoom->students->count() }}

                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                @endforeach

            </tbody>
        </table>






        {!! Setting::get('statistics_for_the_number_of_students_in_classes.pro') !!}

    </div>
    <div class="new-page"></div>
@endsection
