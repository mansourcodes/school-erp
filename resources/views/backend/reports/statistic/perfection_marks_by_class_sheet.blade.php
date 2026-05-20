@extends('backend.layouts.' . $print)


@section('content')
    <div class="page">

        @if (Setting::get('print_header'))
            <img class="w-100" src="{{ URL::asset(Setting::get('print_header')) }}" />
        @endif

        <h3 class="title text-center">
            {{ $course->long_name }}
        </h3>



        {!! Setting::get('students_names_sheet.pre') !!}






        <table class="table table-striped table-bordered">
            <tr>
                <td width="1%">
                    #
                </td>
                <td>
                    Ø§Ù„ÙØµÙ„ Ø§Ù„Ø¯Ø±Ø§Ø³ÙŠ
                </td>
                <td width="10%">
                    Ø¹Ø¯Ø¯ Ø§Ù„Ø¯Ø±Ø¬Ø§Øª Ø§Ù„Ù…Ø±ØµÙˆØ¯Ù‡
                </td>
                <td width="10%">
                    Ù…ØªÙˆØ³Ø· Ø§Ù„Ø¯Ø±Ø¬Ø§Øª
                </td>
                <td width="10%">
                    Ù†Ø³Ø¨Ø© Ø§Ù„Ø±Ø³ÙˆØ¨
                </td>
                <td width="10%">
                    Ù†Ø³Ø¨Ø© Ø§Ù„Ù†Ø¬Ø§Ø­
                </td>
                <td width="10%">
                    Ù†Ø³Ø¨Ø© Ø§Ù„Ø§ØªÙ‚Ø§Ù†
                </td>
            </tr>
            <tr>
                <td width="1%">
                    -
                </td>
                <td>
                    {{ $course->long_name }}
                </td>
                <td width="10%">
                    {{ $courseStatistics['count'] }}
                </td>
                <td width="10%">
                    {{ $courseStatistics['average'] }}
                </td>
                <td width="10%">
                    {{ $courseStatistics['less_than_70_per'] }}%
                </td>
                <td width="10%">
                    {{ $courseStatistics['more_than_70_per'] }}%
                </td>
                <td width="10%">
                    {{ $courseStatistics['more_than_80_per'] }}%
                </td>
            </tr>
        </table>


        <br>
        <h4 class="text-center">
            Ø¥Ø­ØµØ§Ø¦ÙŠØ§Øª Ø§Ù„Ù…ÙˆØ§Ø¯ Ø§Ù„Ø¯Ø±Ø§Ø³ÙŠØ©
        </h4>

        <table class="table table-striped table-bordered">
            <tr>
                <td width="1%">
                    #
                </td>
                <td>
                    Ø§Ù„ØµÙ Ø§Ù„Ø¯Ø±Ø§Ø³ÙŠ
                </td>
                <td width="10%">
                    Ø¹Ø¯Ø¯ Ø§Ù„Ø¯Ø±Ø¬Ø§Øª Ø§Ù„Ù…Ø±ØµÙˆØ¯Ù‡
                </td>
                <td width="10%">
                    Ù…ØªÙˆØ³Ø· Ø§Ù„Ø¯Ø±Ø¬Ø§Øª
                </td>
                <td width="10%">
                    Ù†Ø³Ø¨Ø© Ø§Ù„Ø±Ø³ÙˆØ¨
                </td>
                <td width="10%">
                    Ù†Ø³Ø¨Ø© Ø§Ù„Ù†Ø¬Ø§Ø­
                </td>
                <td width="10%">
                    Ù†Ø³Ø¨Ø© Ø§Ù„Ø§ØªÙ‚Ø§Ù†
                </td>
            </tr>
            @php
                $row_count = 0;
            @endphp
            @foreach ($classRoomCurriculumsStatistics->sortBy('name') as $curriculumStatistics)
                <tr>
                    <td>
                        {{ ++$row_count }}
                    </td>
                    <td>
                        {{ $curriculumStatistics['name'] }}
                    </td>
                    <td>
                        {{ $curriculumStatistics['count'] }}
                    </td>
                    <td>
                        {{ $curriculumStatistics['average'] }}
                    </td>
                    <td>
                        @if (is_numeric($curriculumStatistics['less_than_70_per']))
                            {{ $curriculumStatistics['less_than_70_per'] }}%
                        @else
                            {{ $curriculumStatistics['less_than_70_per'] }}
                        @endif
                    </td>
                    <td>

                        @if (is_numeric($curriculumStatistics['more_than_70_per']))
                            {{ $curriculumStatistics['more_than_70_per'] }}%
                        @else
                            {{ $curriculumStatistics['more_than_70_per'] }}
                        @endif

                    </td>
                    <td>

                        @if (is_numeric($curriculumStatistics['more_than_80_per']))
                            {{ $curriculumStatistics['more_than_80_per'] }}%
                        @else
                            {{ $curriculumStatistics['more_than_80_per'] }}
                        @endif

                    </td>
                </tr>
                @if (request()->has('debug'))
                    <tr>
                        <td colspan="7">
                            <strong>Students:</strong>
                            @foreach ($curriculumStatistics['marks'] as $mark)
                                {{ $mark['total_mark'] }} ,
                            @endforeach
                        </td>
                    </tr>
                @endif
            @endforeach

        </table>
        {!! Setting::get('students_names_sheet.pro') !!}


    </div>
@endsection
