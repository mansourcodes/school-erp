@extends('backend.layouts.'.$print)


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
                {{$student->name}}<br />
                <b>Ø§Ù„Ø±Ù‚Ù… Ø§Ù„Ø´Ø®ØµÙŠ: {{$student->cpr}}</b> 
                <b>Ø±Ù‚Ù… Ø§Ù„Ø·Ø§Ù„Ø¨: {{$student->student_id}}</b> 
                <b>Ø§Ù„Ù†Ù‚Ø§Ù„: {{$student->mobile}}  	</b> 
            </div>


        @endforeach
@endforeach
@endsection
