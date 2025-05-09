@extends('layouts.' . $print)


@section('content')
    @foreach ($classRooms as $classRoom)
        @foreach ($classRoom->curriculums as $curriculum_id => $curriculum)
            <div class="page">

                @if (Setting::get('print_header'))
                    <img class="w-100" src="{{ URL::asset(Setting::get('print_header')) }}" />
                @endif

                <h1 class="title text-center">
                    {{ $course->long_name }}
                </h1>

                <h3 class="title text-center">
                    {{ empty(Setting::get('remember_marks_template.title')) ? __('reports.remember_marks_template') : Setting::get('remember_marks_template.title') }}
                </h3>


                {!! Setting::get('remember_marks_template.pre') !!}

                <table class="table table-striped table-bordered">
                    <tbody>
                        <tr>
                            <td>
                                {{ $classRoom->long_name[$curriculum_id] }}
                            </td>
                        </tr>
                    </tbody>
                </table>


                <table style="table-layout: auto;" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th width="1%">#</th>
                            <th width="20%">اسم الطالب</th>
                            <th>الدرجة النهائية</th>
                            @if (isset($curriculum['curriculum']->marks_labels['memorize_mark_details']))
                                @foreach ($curriculum['curriculum']->marks_labels['memorize_mark_details'] as $memorize_mark_details)
                                    <th>
                                        {{ $memorize_mark_details->label ?? '' }}
                                        {{ $memorize_mark_details->mark ?? '' }}
                                    </th>
                                @endforeach
                            @endif


                        </tr>
                    </thead>
                    <tbody>

                        @foreach ($classRoom->students as $key => $student)
                            <tr>
                                <td>{{ ++$key }}</td>
                                <td>
                                    {{ $student->name }}
                                </td>
                                <td></td>
                                @if (isset($curriculum['curriculum']->marks_labels['memorize_mark_details']))
                                    @foreach ($curriculum['curriculum']->marks_labels['memorize_mark_details'] as $memorize_mark_details)
                                        <td></td>
                                    @endforeach
                                @endif

                            </tr>
                        @endforeach
                    </tbody>


                    </tbody>
                </table>





                {!! Setting::get('remember_marks_template.pro') !!}

            </div>
            <div class="new-page"></div>
        @endforeach
    @endforeach
@endsection
