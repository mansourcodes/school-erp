@extends('layouts.' . $print)


@section('content')
    @foreach ($classRooms as $classRoom)
        @foreach ($classRoom->students as $student)
            <div class="page">

                @include('reports.student.components.student_table_template')


            </div>
            <div class="new-page"></div>
        @endforeach
    @endforeach
@endsection
