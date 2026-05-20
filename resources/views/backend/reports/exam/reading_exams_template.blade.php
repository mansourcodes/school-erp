@extends('backend.layouts.' . $print)


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
                    {{ empty(Setting::get('reading_exams_template.title')) ? __('reports.reading_exams_template') : Setting::get('reading_exams_template.title') }}
                </h3>


                {!! Setting::get('reading_exams_template.pre') !!}

                <table class="table table-striped table-bordered">
                    <tbody>
                        <tr>
                            <td>
                                Ø§Ù„ØµÙ
                            </td>
                            <td>
                                {{ $classRoom->long_name[$curriculum_id] }}
                            </td>
                            <td>
                                Ø§Ù„ØªØ§Ø±ÙŠØ® :
                            </td>
                        </tr>
                    </tbody>
                </table>


                <table style="table-layout: auto;" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ØªØ¬ÙˆÙŠØ¯</th>
                            <th>Ù‚Ø±Ø§Ø¡Ø©</th>
                            <th width="40%">Ø§Ø³Ù… Ø§Ù„Ø·Ø§Ù„Ø¨</th>
                            <th>Ø§Ù„Ø­Ø¶ÙˆØ±</th>
                            <th>Ø§Ù„Ù‡Ø§ØªÙ</th>
                            <th>Ø§Ù„Ù‡Ø§ØªÙÙ¢</th>


                        </tr>
                    </thead>
                    <tbody>

                        @foreach ($classRoom->students as $key => $student)
                            <tr>
                                <td></td>
                                <td></td>
                                <td>
                                    {{ $student->name }}
                                </td>
                                <td></td>
                                <td> {{ $student->mobile }} </td>
                                <td> {{ $student->mobile2 }} </td>

                            </tr>
                        @endforeach
                    </tbody>

                </table>


                <table class="table table-striped table-bordered">
                    <tbody>
                        <tr>
                            <td width="25%">
                                Ø§Ø³Ù… Ø§Ù„Ù…Ø±Ø§Ù‚Ø¨
                            </td>
                            <td> </td>
                            <td width="25%">
                                Ø§Ù„Ø¹Ø¯Ø¯ Ø§Ù„ÙƒÙ„ÙŠ
                            </td>
                            <td> </td>
                        </tr>
                        <tr>
                            <td>
                                Ø¹Ø¯Ø¯ Ø§Ù„Ù…Ù‚Ø¯Ù…ÙŠÙ†
                            </td>
                            <td> </td>
                            <td>
                                Ø¹Ø¯Ø¯ ØºÙŠØ± Ø§Ù„Ù…Ù‚Ø¯Ù…ÙŠÙ†
                            </td>
                            <td> </td>
                        </tr>
                        <tr>
                            <td>
                                Ø¹Ø¯Ø¯ Ø§Ù„Ø·Ù„Ø¨Ø© Ø§Ù„Ù†Ø§Ø¬Ø­ÙŠÙ†
                            </td>
                            <td> </td>
                            <td>
                                Ø¹Ø¯Ø¯ Ø§Ù„Ø·Ù„Ø¨Ø© Ø§Ù„Ø±Ø§Ø³Ø¨ÙŠÙ†
                            </td>
                            <td> </td>
                        </tr>
                        <tr>
                            <td>
                                Ø§Ø³Ù… Ø§Ù„Ù…ØµØ­Ø­
                            </td>
                            <td> </td>
                            <td>
                                Ø§Ø³Ù… Ø§Ù„Ù…Ø±Ø§Ø¬Ø¹
                            </td>
                            <td> </td>
                        </tr>
                    </tbody>
                </table>





                {!! Setting::get('reading_exams_template.pro') !!}

            </div>

            @if (!$loop->parent->last)
                <div class="new-page"></div>
            @endif
        @endforeach
    @endforeach
@endsection
