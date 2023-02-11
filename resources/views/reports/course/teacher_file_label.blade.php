@extends('layouts.' . $print)


@section('content')
    @foreach ($sessions as $session_group)
        @foreach ($session_group as $session)
            <table class="table table-striped table-bordered">
                <tbody>
                    <tr>
                        <td width="25%">
                            {{ $session['long_name'] }}
                            {{-- {{ $session['curriculum']['teacher_name'] ?? '' }}
                            /
                            صف {{ $session['class_room_number'] }}
                             --}}
                        </td>
                        <td width="20%">

                            {{ $session['curriculum']['curriculumـname'] ?? '' }}
                        </td>

                        @foreach ($session['curriculum']['days'] as $day)
                            <td> {{ $weekDays[$day] ?? '' }} {{ $session['curriculum']['attend_table'][$day] ?? '' }} </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        @endforeach
        <hr>
        <div class="new-page"></div>
    @endforeach
@endsection
