@extends('backend.layouts.' . $print)


@section('content')
    @foreach ($classRooms as $classRoom)
        @foreach ($classRoom->curriculums as $key => $curriculum)
            <div class="page">

                @if (Setting::get('print_header'))
                    <img class="w-100" src="{{ URL::asset(Setting::get('print_header')) }}" />
                @endif

                <h1 class="title text-center">
                    {{ $course->long_name }}
                </h1>

                <h3 class="title text-center">
                    {{ empty(Setting::get('teacher_table.title')) ? __('reports.teacher_table') : Setting::get('teacher_table.title') }}
                </h3>


                {!! Setting::get('teacher_table.pre') !!}

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
                            <td>Ø§Ø³Ù… Ø§Ù„Ù…Ø¯Ø±Ø³</td>
                            <td>{{ $curriculum['teacher_name'] ?? '' }}</td>
                        </tr>
                        <tr>
                            <td>Ø±Ù‚Ù… Ø§Ù„ØµÙ</td>
                            <td>{{ $classRoom->class_room_number }}</td>
                        </tr>
                        <tr>
                            <td>Ù…ÙƒØ§Ù† Ø§Ù„Ø¯Ø±Ø§Ø³Ø©</td>
                            <td>{{ $classRoom->class_room_name }}</td>
                        </tr>
                        <tr>
                            <td>ÙŠÙˆÙ… ÙˆØªØ§Ø±ÙŠØ® Ø¨Ø¯Ø¡ Ø§Ù„Ø¯Ø±Ø§Ø³Ø©</td>
                            <td>{{ $course->start_date->format('d-m-Y') }}</td>
                        </tr>
                        <tr>
                            <td>ÙŠÙˆÙ… ÙˆØªØ§Ø±ÙŠØ® Ù†Ù‡Ø§ÙŠØ© Ø§Ù„Ø¯Ø±Ø§Ø³Ø©</td>
                            <td>{{ $course->end_date->format('d-m-Y') }}</td>
                        </tr>
                        <tr>
                            <td>Ø¹Ø¯Ø¯ Ø³Ø§Ø¹Ø§Øª Ø§Ù„Ù…Ù‚Ø±Ø±Ø©</td>
                            <td>{{ $course->duration }}</td>
                        </tr>
                        <tr>
                            <td>ÙˆÙ‚Øª Ø§Ù„Ø¯Ø±Ø§Ø³Ø©</td>
                            <td>
                                @isset($curriculum['days'])
                                    @foreach ($curriculum['days'] as $day)
                                        {{ $weekDays[$day] ?? '' }} {{ $curriculum['attend_table'][$day] ?? '' }} <br>
                                    @endforeach
                                @endisset
                            </td>
                        </tr>
                    </tbody>
                </table>



                {!! Setting::get('teacher_table.pro') !!}

            </div>

            @if (!$loop->parent->last)
                <div class="new-page"></div>
            @endif
        @endforeach
    @endforeach
@endsection
