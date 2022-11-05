@extends('layouts.'.$print)


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


            <div class="card_c" >
                {{$classRoom->long_name}}<br />
                {{$student->student_name}}<br />
                <b>الرقم الشخصي: {{$student->cpr}}</b> 
                <b>رقم الطالب: {{$student->student_id}}</b> 
                <b>النقال: {{$student->mobile}}  	</b> 
            </div>


        @endforeach
@endforeach
@endsection
