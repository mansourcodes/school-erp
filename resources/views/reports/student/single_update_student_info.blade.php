@extends('layouts.' . $print)


@section('content')
    @foreach ($classRooms as $classRoom)
        <div class="page">

            @include('reports.student.components.update_student_info_template')


        </div>
        @if (!$loop->last)
            <div class="new-page"></div>
        @endif
    @endforeach
@endsection
