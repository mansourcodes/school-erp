@extends('layouts.' . $print)


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
                                <td>الاسم</td>
                                <th> {{ $student->student_name }}</th>
                            </tr>
                            <tr>
                                <td>اسم المدرس</td>
                                <td>{{ $curriculum['teacher_name'] ?? '' }}</td>
                            </tr>
                            <tr>
                                <td>المنهج</td>
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
                                <td colspan="5">درجة التقييم</td>
                            </tr>
                            <tr>
                                <td style="width: 16%">جوانب التقييم</td>
                                <td style="width: 16%">ممتاز</td>
                                <td style="width: 16%">جيد جدا</td>
                                <td style="width: 16%">جيد</td>
                                <td style="width: 16%">متوسط</td>
                                <td style="width: 16%">ضعيف</td>
                            </tr>
                            <tr>
                                <td>القراءة</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>التجويد</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>الحفظ</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>السلوك</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>الحضور</td>
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
                                <td>ملاحظة المدرس</td>
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
                                    اعتمدها رئيس المركز :

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
