@extends('backend.layouts.' . $print)


@section('content')
    @foreach ($studentmarks as $studentmark)
        <div class="page certificate-frame flex-column-space-between">


            <div style="width: 100%">

                <div class="w-100 text-center ">
                    <img src="{{ asset('img/alghadeerquran_logo.jpg') }}" alt="Logo">
                </div>

                <div class="w-100 text-center"> {{ $studentmark->course->long_name }} </div>

                <h1 class="title text-center">
                    {{ Setting::get('certificate.title') == '' ? __('reports.certificate') : Setting::get('certificate.title') }}
                </h1>
                <hr>
                <h3 class="text-center">
                    {{ $studentmark->student->name }}
                </h3>


                <span>
                    {!! Setting::get('certificate.pre') !!}
                </span>

                <table class="table table-no-border" style="width: 50%; margin: 0 auto">
                    <tr>
                        <th> Ø§Ù„Ø±Ù‚Ù… Ø§Ù„Ø´Ø®ØµÙŠ:</th>
                        <td> {{ $studentmark->student->cpr > 9 ? 'Ù„Ø§ÙŠÙˆØ¬Ø¯' : $studentmark->student->cpr }}
                        </td>
                    </tr>
                    <tr>
                        <th> Ø±Ù‚Ù… Ø§Ù„Ø·Ø§Ù„Ø¨:</th>
                        <td> {{ $studentmark->student->student_id }}</td>
                    </tr>
                    <tr>
                        <th> Ø§Ù„Ù…Ù†Ù‡Ø¬:</th>
                        <td> {{ $studentmark->curriculum->curriculum_name }}</td>
                    </tr>
                </table>

            </div>

            @include('backend.reports.certificate_table', [
                'studentmark' => $studentmark,
                'table_width' => '50%',
            ])


            <span>
                {!! Setting::get('certificate.pro') !!}
            </span>


            <table class="table table-no-border" style="width: 100%">
                <tr>
                    <th> Ø§Ø¹ØªÙ…Ø¯Ù‡Ø§ Ø±Ø¦ÙŠØ³ Ø§Ù„Ù…Ø±ÙƒØ²</th>
                    <th class="text-left"> Ø®ØªÙ… Ø§Ù„Ù…Ø±ÙƒØ²</th>
                </tr>
                <tr>
                    <td>
                        {!! Setting::get('ceo_name') !!}
                    </td>
                    <td class="text-left">
                        Ø­Ø±Ø±Øª ÙÙŠ
                        {{ $studentmark->created_at->format('Y-m-d') }} Ù…
                    </td>
                </tr>

            </table>


        </div>
        @if (!$loop->last)
            <div class="new-page"></div>
        @endif
    @endforeach
@endsection
