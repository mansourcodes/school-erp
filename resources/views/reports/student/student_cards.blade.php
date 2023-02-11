@extends('layouts.' . $print)


@section('content')
    <style>
        .card_c {
            display: inline-block;
            margin: 0;
            width: 96%;
            text-align: right;
            border: 0px solid #000;
            padding: 0 15px;
            font-size: 18px;
            line-height: normal;
            height: 100px;
        }
    </style>

    @foreach ($classRooms as $classRoom)
        @foreach ($classRoom->students as $student)
            @foreach ($classRoom->long_name as $long_name)
                <div class="card_c">
                    {{ $long_name }}<br />
                    {{ $student->student_name }}<br />
                    <b>الرقم الشخصي: {{ $student->cpr }}</b><br />
                    <b>رقم الطالب: {{ $student->student_id }}</b><br />
                    <b>النقال: {{ $student->mobile }} </b>
                </div>
            @endforeach
        @endforeach
    @endforeach
@endsection
