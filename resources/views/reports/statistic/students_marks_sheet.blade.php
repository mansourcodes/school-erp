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
                    {{ empty(Setting::get('students_names_sheet.title')) ? '' : Setting::get('students_names_sheet.title') . ' - ' }}
                    {{ $classRoom->long_name[$key] }}
                </h3>


                {!! Setting::get('students_names_sheet.pre') !!}



                <table style="table-layout: auto;" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th width="1%">#</th>
                            <th>اسم الطالب</th>
                            @foreach ($curriculum['curriculum']->marks_labels_flat as $marks_label)
                                <th>{{ $marks_label }}</th>
                            @endforeach
                            <th>المجموع</th>

                        </tr>
                    </thead>
                    <tbody>

                        @foreach ($classRoom->students as $key => $student)
                            <tr>
                                <td>{{ ++$key }}</td>
                                <td>
                                    {{ $student->name }}
                                </td>
                                @php
                                    $student_marks = $student->getMarksByCourse($course->id);
                                @endphp
                                @if ($student_marks)
                                    @foreach ($student_marks->brief_marks as $mark)
                                        <td>
                                            {{-- {{ $mark->label }} --}}
                                            {{ $mark->mark }}
                                            {{-- {{ $mark->max }} --}}
                                        </td>
                                    @endforeach
                                    <td>
                                        {{ $student_marks->total_mark }}
                                    </td>
                                @else
                                    @foreach ($curriculum['curriculum']->marks_labels_flat as $marks_label)
                                        <td></td>
                                    @endforeach
                                    <td></td>
                                @endif



                            </tr>
                        @endforeach
                    </tbody>


                    </tbody>
                </table>





                {!! Setting::get('students_names_sheet.pro') !!}

            </div>
            @if (!$loop->parent->last)
                <div class="new-page"></div>
            @endif
        @endforeach
    @endforeach
@endsection
