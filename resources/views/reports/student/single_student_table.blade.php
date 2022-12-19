@extends('layouts.' . $print)


@section('content')
    @foreach ($classRooms as $classRoom)
        <div class="page">

            @include('reports.student.components.student_table_template')

        </div>
        @if (!$loop->last)
            <div class="new-page"></div>
        @endif
    @endforeach
@endsection
