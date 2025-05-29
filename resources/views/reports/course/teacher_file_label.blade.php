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

                            {{ $session['curriculum']['curriculum_name'] ?? '' }}
                        </td>
                        @isset($session['curriculum']['days'])
                            @foreach ($session['curriculum']['days'] as $day)
                                <td> {{ $weekDays[$day] ?? '' }} {{ $session['curriculum']['attend_table'][$day] ?? '' }} </td>
                            @endforeach
                        @endisset

                    </tr>
                </tbody>
            </table>
        @endforeach
        <hr>

        @if (!$loop->last)
            <div class="new-page"></div>
        @endif
    @endforeach
@endsection
