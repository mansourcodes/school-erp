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

                {{-- <table class="table table-striped table-bordered">
                    <tbody>
                        <tr>
                            <td>
                                {{ $classRoom->long_name[$key] }}</td>
                        </tr>
                    </tbody>
                </table> --}}


                <table style="table-layout: auto;" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th width="1%">#</th>
                            <th>اسم الطالب</th>
                            <th width="20%">الهاتف</th>
                            <th width="20%">الهاتف 2</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach ($classRoom->students as $key => $student)
                            <tr>
                                <td>{{ ++$key }}</td>
                                <td>
                                    {{ $student->name }}
                                </td>
                                <td>{{ $student->mobile }} </td>
                                <td>{{ $student->mobile2 }}</td>
                            </tr>
                        @endforeach
                    </tbody>


                    </tbody>
                </table>





                {!! Setting::get('students_names_sheet.pro') !!}

            </div>
            <div class="new-page"></div>
        @endforeach
    @endforeach
@endsection
