@extends('layouts.'.$print)


@section('content')
@foreach ($studentmarks as $studentmark)

<div class="page">

    @if(Setting::get('print_header'))
    <img class="w-100" src="{{URL::asset(Setting::get('print_header'))}}" />
    @endif

    <h1 class="title text-center">
        {{Setting::get('update_students_info.title') === '' ? __('reports.update_students_info') : Setting::get('update_students_info.title') }}
    </h1>

    {!! Setting::get('update_students_info.pre') !!}

    --Content --

    {!! Setting::get('update_students_info.pro') !!}

</div>
<div class="new-page"></div>


@endforeach
@endsection
