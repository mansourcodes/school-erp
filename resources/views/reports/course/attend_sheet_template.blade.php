@extends('layouts.' . $print)


@section('content')
    @foreach ($classRooms as $classRoom)
        @foreach ($classRoom->curriculums as $key => $curriculum)
            <div class="page">

                @if (Setting::get('print_header'))
                    <img class="w-100" src="{{ URL::asset(Setting::get('print_header')) }}" />
                @endif

                <h1 class="title text-center">
                    {{ $course->long_name[$key] }}
                </h1>

                <h3 class="title text-center">
                    {{ empty(Setting::get('attend_sheet_template.title')) ? __('reports.attend_sheet_template') : Setting::get('attend_sheet_template.title') }}
                </h3>


                {!! Setting::get('attend_sheet_template.pre') !!}

                <table class="table table-striped table-bordered">
                    <tbody>
                        <tr>
                            <td>
                                {{ $classRoom->long_name[$key] }}</td>
                        </tr>
                    </tbody>
                </table>


                <table style="table-layout: auto;" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th width="1%">#</th>
                            <th width="20%">اسم الطالب</th>
                            <th>الهاتف</th>
                            <th> / / </th>
                            <th> / / </th>
                            <th> / / </th>
                            <th> / / </th>
                            <th> / / </th>
                            <th> / / </th>
                            <th> / / </th>
                            <th> / / </th>
                            <th> / / </th>
                            <th> / / </th>
                            <th> / / </th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach ($classRoom->students as $key => $student)
                            <tr>
                                <td>{{ ++$key }}</td>
                                <td>
                                    {{ $student->student_name }}
                                </td>
                                <td>{{ $student->mobile }} - {{ $student->mobile2 }}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        @endforeach
                    </tbody>


                    </tbody>
                </table>





                {!! Setting::get('attend_sheet_template.pro') !!}

            </div>
            <div class="new-page"></div>
        @endforeach
    @endforeach
@endsection
