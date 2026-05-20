@extends('backend.layouts.' . $print)


@section('content')
    @foreach ($classRooms as $classRoom)
        @foreach ($classRoom->curriculums as $curriculum_id => $curriculum)
            @foreach ($classRoom->students as $student)
                <div class="page">

                    @if (Setting::get('print_header'))
                        <img class="w-100" src="{{ URL::asset(Setting::get('print_header')) }}" />
                    @endif

                    <h3 class="title text-center">
                        {{ empty(Setting::get('registration_form.title')) ? __('reports.registration_form') : Setting::get('registration_form.title') }}
                        - {{ $course->long_name }}
                    </h3>


                    {!! Setting::get('registration_form.pre') !!}


                    <table dir="rtl" border="1" width="394" cellspacing="0" cellpadding="0" style="margin: auto;">
                        <tbody>
                            <tr>
                                <td width="134">
                                    <p dir="RTL">Ø¨Ø¯Ø§ÙŠØ© Ø§Ù„Ø¯ÙˆØ±Ø©</p>
                                </td>
                                <td width="260">
                                    <p dir="RTL">
                                        {{ $weekDays[$course->start_date->format('N')] ?? '' }}
                                        {{ $course->start_date->format('d-m-Y') }}
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <td>Ø¹Ø¯Ø¯ Ø³Ø§Ø¹Ø§Øª Ø§Ù„Ù…Ù‚Ø±Ø±Ø©</td>
                                <td>{{ $course->duration }}</td>
                            </tr>
                            <tr>
                                <td width="134">
                                    <p dir="RTL">Ø§Ù„Ø±Ø³ÙˆÙ…</p>
                                </td>
                                <td width="260">
                                    <p dir="RTL">20 Ø¯ÙŠÙ†Ø§Ø±Ø§ Ù„Ù„ÙØµÙ„ Ø§Ù„Ø§ÙˆÙ„</p>
                                </td>
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


                    <br>

                    {!! Setting::get('registration_form.pro') !!}

                    <br>
                    <p dir="RTL" style="margin: auto;text-align:center">
                        ------------------------------------------------------------------------------------------------------------------------
                        <span
                            style="color: black; font-family: 'Wingdings 2'; font-size: 12pt; line-height: 1.3em;">%</span>
                    </p>
                    <br>


                    <h3 class="title text-center">
                        {{ empty(Setting::get('registration_form.title')) ? __('reports.registration_form') : Setting::get('registration_form.title') }}
                        - {{ $course->long_name }}
                    </h3>

                    <table dir="rtl" style="margin: auto;" style="padding: 20px;" border="1" width="400"
                        cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td style="padding: 20px;">
                                    <table dir="rtl" border="0" width="" cellspacing="0" cellpadding="0">
                                        <tbody>
                                            <tr>
                                                <td colspan="10" valign="top" width="743">
                                                    <p dir="RTL">
                                                        {{ $student->name }}
                                                    </p>
                                                    <p dir="RTL">

                                                        {{ $classRoom->long_name[$curriculum_id] }}

                                                    </p>
                                                    <div align="right">
                                                        <table dir="rtl" border="0" width="394" cellspacing="0"
                                                            cellpadding="0">
                                                            <tbody>
                                                                <tr>
                                                                    <td valign="top" width="125">
                                                                        <p dir="RTL">Ø§Ù„Ø±Ù‚Ù… Ø§Ù„Ø´Ø®ØµÙŠ:</p>
                                                                    </td>
                                                                    <td valign="top" width="426">
                                                                        <p dir="RTL">
                                                                            {{ $student->cpr }}
                                                                        </p>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td valign="top" width="125">
                                                                        <p dir="RTL">Ø±Ù‚Ù… Ø§Ù„Ø·Ø§Ù„Ø¨ :</p>
                                                                    </td>
                                                                    <td valign="top" width="426">
                                                                        <p dir="RTL">{{ $student->mobile }}</p>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td valign="top" width="125">
                                                                        <p dir="RTL">Ø±Ù‚Ù… Ø§Ù„Ù†Ù‚Ø§Ù„:</p>
                                                                    </td>
                                                                    <td valign="top" width="426">
                                                                        <p dir="RTL">{{ $student->mobile2 }}</p>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td valign="top" width="74">&nbsp;</td>
                                                <td valign="top" width="74">&nbsp;</td>
                                                <td valign="top" width="74">&nbsp;</td>
                                                <td valign="top" width="74">&nbsp;</td>
                                                <td valign="top" width="74">&nbsp;</td>
                                                <td valign="top" width="74">&nbsp;</td>
                                                <td valign="top" width="74">&nbsp;</td>
                                                <td valign="top" width="74">&nbsp;</td>
                                                <td valign="top" width="74">&nbsp;</td>
                                                <td valign="top" width="74">&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <td valign="top" width="74">
                                                    <p dir="RTL" style="text-align: center;" align="right"><span
                                                            style="font-size: 12.0pt; font-family: 'Wingdings 2'; mso-ascii-font-family: Arial; mso-ascii-theme-font: minor-bidi; mso-fareast-font-family: 'Times New Roman'; mso-hansi-font-family: Arial; mso-hansi-theme-font: minor-bidi; mso-bidi-font-family: Arial; mso-bidi-theme-font: minor-bidi; color: black; mso-themecolor: text1; mso-ansi-language: EN-US; mso-fareast-language: EN-US; mso-bidi-language: AR-BH; mso-char-type: symbol; mso-symbol-font-family: 'Wingdings 2';">Â£</span>
                                                    </p>
                                                </td>
                                                <td colspan="9" valign="top" width="668">
                                                    <p dir="RTL"><strong>Ø£ÙˆØ§ÙÙ‚ Ø¹Ù„Ù‰ ØªØ³Ø¬ÙŠÙ„ Ø§Ø¨Ù†ÙŠ .</strong></p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td valign="top" width="74">
                                                    <p dir="RTL" style="text-align: center;" align="right"><span
                                                            style="font-size: 12.0pt; font-family: 'Wingdings 2'; mso-ascii-font-family: Arial; mso-ascii-theme-font: minor-bidi; mso-fareast-font-family: 'Times New Roman'; mso-hansi-font-family: Arial; mso-hansi-theme-font: minor-bidi; mso-bidi-font-family: Arial; mso-bidi-theme-font: minor-bidi; color: black; mso-themecolor: text1; mso-ansi-language: EN-US; mso-fareast-language: EN-US; mso-bidi-language: AR-BH; mso-char-type: symbol; mso-symbol-font-family: 'Wingdings 2';">Â£</span>
                                                    </p>
                                                </td>
                                                <td colspan="9" valign="top" width="668">
                                                    <p dir="RTL"><strong>Ù„Ø§ Ø£ÙˆØ§ÙÙ‚ Ø¹Ù„Ù‰ ØªØ³Ø¬ÙŠÙ„ Ø§Ø¨Ù†ÙŠ .</strong></p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td valign="top" width="74">&nbsp;</td>
                                                <td valign="top" width="74">&nbsp;</td>
                                                <td valign="top" width="74">&nbsp;</td>
                                                <td valign="top" width="74">&nbsp;</td>
                                                <td valign="top" width="74">&nbsp;</td>
                                                <td valign="top" width="74">&nbsp;</td>
                                                <td valign="top" width="74">&nbsp;</td>
                                                <td colspan="2" width="149">
                                                    <p dir="RTL" align="center">ØªÙˆÙ‚ÙŠØ¹ ÙˆÙ„ÙŠ Ø§Ù„Ø£Ù…Ø±</p>
                                                    <p dir="RTL" align="center">___________________</p>
                                                </td>
                                                <td valign="top" width="74">&nbsp;</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>






                </div>
                <div class="new-page"></div>
            @endforeach
        @endforeach
    @endforeach
@endsection
