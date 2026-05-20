@extends('backend.layouts.' . $print)


@section('content')
    @foreach ($classRooms as $classRoom)
        @foreach ($classRoom->curriculums as $curriculum_id => $curriculum)
            @foreach ($classRoom->students as $student)
                <div class="page">

                    @if (Setting::get('print_header'))
                        <img class="w-100" src="{{ URL::asset(Setting::get('print_header')) }}" />
                    @endif

                    <h1 class="title text-center">
                        {{ $course->long_name }}
                    </h1>

                    <h3 class="title text-center">
                        {{ empty(Setting::get('parent_meeting_template.title')) ? __('reports.parent_meeting_template') : Setting::get('parent_meeting_template.title') }}
                    </h3>


                    {!! Setting::get('parent_meeting_template.pre') !!}


                    <table class="table table-striped table-bordered">
                        <tbody>
                            <tr>
                                <td>Ø§Ù„Ø§Ø³Ù…</td>
                                <th> {{ $student->name }}</th>
                            </tr>
                            <tr>
                                <td>Ø§Ø³Ù… Ø§Ù„Ù…Ø¯Ø±Ø³</td>
                                <td>{{ $curriculum['teacher_name'] ?? '' }}</td>
                            </tr>
                            <tr>
                                <td>Ø§Ù„Ù…Ù†Ù‡Ø¬</td>
                                <td>{{ $curriculum['curriculum_name'] ?? '' }}</td>
                            </tr>
                        </tbody>
                        <tbody>
                        </tbody>
                    </table>


                    <table class="table table-striped table-bordered">


                        <tbody>
                            <tr>
                                <td></td>
                                <td colspan="5">Ø¯Ø±Ø¬Ø© Ø§Ù„ØªÙ‚ÙŠÙŠÙ…</td>
                            </tr>
                            <tr>
                                <td style="width: 16%">Ø¬ÙˆØ§Ù†Ø¨ Ø§Ù„ØªÙ‚ÙŠÙŠÙ…</td>
                                <td style="width: 16%">Ù…Ù…ØªØ§Ø²</td>
                                <td style="width: 16%">Ø¬ÙŠØ¯ Ø¬Ø¯Ø§</td>
                                <td style="width: 16%">Ø¬ÙŠØ¯</td>
                                <td style="width: 16%">Ù…ØªÙˆØ³Ø·</td>
                                <td style="width: 16%">Ø¶Ø¹ÙŠÙ</td>
                            </tr>
                            <tr>
                                <td>Ø§Ù„Ù‚Ø±Ø§Ø¡Ø©</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Ø§Ù„ØªØ¬ÙˆÙŠØ¯</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Ø§Ù„Ø­ÙØ¸</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Ø§Ù„Ø³Ù„ÙˆÙƒ</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Ø§Ù„Ø­Ø¶ÙˆØ±</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>


                    <table class="table table-striped table-bordered">
                        <tbody>
                            <tr>
                                <td>Ù…Ù„Ø§Ø­Ø¸Ø© Ø§Ù„Ù…Ø¯Ø±Ø³</td>
                            </tr>
                            <tr>
                                <td>
                                    <br><br><br><br>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <td>
                                    Ø§Ø¹ØªÙ…Ø¯Ù‡Ø§ Ø±Ø¦ÙŠØ³ Ø§Ù„Ù…Ø±ÙƒØ² :

                                    <b> {!! Setting::get('ceo_name') !!}</b>
                                </td>
                            </tr>
                        </tbody>
                    </table>





                    {!! Setting::get('parent_meeting_template.pro') !!}

                </div>
                <div class="new-page"></div>
            @endforeach
        @endforeach
    @endforeach
@endsection
