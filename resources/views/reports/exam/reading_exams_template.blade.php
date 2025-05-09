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
                    {{ empty(Setting::get('reading_exams_template.title')) ? __('reports.reading_exams_template') : Setting::get('reading_exams_template.title') }}
                </h3>


                {!! Setting::get('reading_exams_template.pre') !!}

                <table class="table table-striped table-bordered">
                    <tbody>
                        <tr>
                            <td>
                                الصف
                            </td>
                            <td>
                                {{ $classRoom->long_name[$curriculum_id] }}
                            </td>
                            <td>
                                التاريخ :
                            </td>
                        </tr>
                    </tbody>
                </table>


                <table style="table-layout: auto;" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>تجويد</th>
                            <th>قراءة</th>
                            <th width="40%">اسم الطالب</th>
                            <th>الحضور</th>
                            <th>الهاتف</th>
                            <th>الهاتف٢</th>


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
                                اسم المراقب
                            </td>
                            <td> </td>
                            <td width="25%">
                                العدد الكلي
                            </td>
                            <td> </td>
                        </tr>
                        <tr>
                            <td>
                                عدد المقدمين
                            </td>
                            <td> </td>
                            <td>
                                عدد غير المقدمين
                            </td>
                            <td> </td>
                        </tr>
                        <tr>
                            <td>
                                عدد الطلبة الناجحين
                            </td>
                            <td> </td>
                            <td>
                                عدد الطلبة الراسبين
                            </td>
                            <td> </td>
                        </tr>
                        <tr>
                            <td>
                                اسم المصحح
                            </td>
                            <td> </td>
                            <td>
                                اسم المراجع
                            </td>
                            <td> </td>
                        </tr>
                    </tbody>
                </table>





                {!! Setting::get('reading_exams_template.pro') !!}

            </div>
            <div class="new-page"></div>
        @endforeach
    @endforeach
@endsection
