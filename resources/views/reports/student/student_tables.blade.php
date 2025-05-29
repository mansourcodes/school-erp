@extends('layouts.' . $print)


@section('content')
    @foreach ($classRooms as $classRoom)
        @foreach ($classRoom->students as $student)
            <div class="page">

                @include('reports.student.components.student_table_template')


            </div>

            @if (!$loop->parent->last)
                <div class="new-page"></div>
            @endif
        @endforeach
    @endforeach
@endsection
