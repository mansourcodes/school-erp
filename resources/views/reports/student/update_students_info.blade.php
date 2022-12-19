@extends('layouts.' . $print)


@section('content')
    @foreach ($classRooms as $classRoom)
        @foreach ($classRoom->students as $student)
            <div class="page">

                @include('reports.student.components.update_student_info_template')


            </div>
            <div class="new-page"></div>
        @endforeach
    @endforeach
@endsection
