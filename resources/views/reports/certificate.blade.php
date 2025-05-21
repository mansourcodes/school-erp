@extends('layouts.' . $print)


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
                        <th> الرقم الشخصي:</th>
                        <td> {{ $studentmark->student->cpr > 9 ? 'لايوجد' : $studentmark->student->cpr }}
                        </td>
                    </tr>
                    <tr>
                        <th> رقم الطالب:</th>
                        <td> {{ $studentmark->student->student_id }}</td>
                    </tr>
                    <tr>
                        <th> المنهج:</th>
                        <td> {{ $studentmark->curriculum->curriculum_name }}</td>
                    </tr>
                </table>

            </div>


            <div style="width: 50%">
                <table class="table table-no-border" style="width: 100%">

                    <tr>
                        <th> الدرجات:</th>
                        <td> </td>
                    </tr>
                </table>


                <table class="table " style="width: 100%">
                    <tr>
                        <th class="text-center">
                            الوصف
                        </th>
                        <th class="text-center">
                            الدرجة العظمى
                        </th>
                        <th class="text-center">
                            الدرجة المستحقة
                        </th>
                    </tr>
                    @foreach ($studentmark->brief_marks as $mark)
                        {{-- @dd($mark) --}}
                        <tr>
                            <td class="text-center">
                                @if (isArabic($mark->label))
                                    {{ $mark->label }}
                                @else
                                    {{ __('studentmark.' . $mark->label) }}
                                @endif
                            </td>
                            <td class="text-center">
                                {{ $mark->max }}
                            </td>
                            <td class="text-center">
                                {{ $mark->mark }}
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <th class="text-center">
                            المجموع
                        </th>
                        <th class="text-center">
                            100
                        </th>
                        <th class="text-center">
                            {{ $studentmark->total_mark }}
                        </th>
                    </tr>

                </table>


                <table class="table table-no-border" style="width: 100%">
                    <tr>
                        <th> التقدير العام:</th>
                        <td> {{ $studentmark->final_grade }}</td>
                    </tr>

                </table>
                <hr>

            </div>


            <span>
                {!! Setting::get('certificate.pro') !!}
            </span>


            <table class="table table-no-border" style="width: 100%">
                <tr>
                    <th> اعتمدها رئيس المركز</th>
                    <th class="text-left"> ختم المركز</th>
                </tr>
                <tr>
                    <td>
                        {!! Setting::get('ceo_name') !!}
                    </td>
                    <td class="text-left">
                        حررت في
                        {{ $studentmark->created_at->format('Y-m-d') }} م
                    </td>
                </tr>

            </table>


        </div>
        @if (!$loop->last)
            <div class="new-page"></div>
        @endif
    @endforeach
@endsection
