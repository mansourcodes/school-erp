@extends('layouts.' . $print)


@section('content')
    @foreach ($classRooms as $classRoom)
        @foreach ($classRoom->curriculums as $curriculum)
            <table class="table table-striped table-bordered">
                <tbody>
                    <tr>
                        <td width="25%">

                            {{ $curriculum['teacher_name'] ?? '' }}
                            /
                            صف {{ $classRoom->class_room_number }}
                        </td>
                        <td width="20%">

                            {{ $curriculum['curriculumـname'] ?? '' }}
                        </td>

                        @foreach ($curriculum['days'] as $day)
                            <td> {{ $weekDays[$day] ?? '' }} {{ $curriculum['attend_table'][$day] ?? '' }} </td>
                        @endforeach


                    </tr>
                </tbody>
                <tbody>
                </tbody>
            </table>
        @endforeach
    @endforeach
@endsection
