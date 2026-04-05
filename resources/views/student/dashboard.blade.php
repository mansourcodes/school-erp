@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">

                {{--  --}}
                <h2>{{ __('localize.student_dashboard') }}</h2>

                <div class="card mb-5">

                    <div class="card-header">

                        <svg style="width:24px" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            strokeWidth={1.5} stroke="currentColor" className="size-6">
                            <path strokeLinecap="round" strokeLinejoin="round"
                                d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>



                        {{ __('localize.welcome') }}, {{ $student->name }}
                    </div>


                    <div class="card-body">

                        <p>

                            <svg style="width:16px" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                strokeWidth={1.5} stroke="currentColor" className="size-6">
                                <path strokeLinecap="round" strokeLinejoin="round"
                                    d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                            </svg>


                            {{ __('localize.student_id') }}: {{ $student->student_id }}
                        </p>
                        <p>

                            <svg style="width:16px" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.336a6.721 6.721 0 0 1-3.17.789 6.721 6.721 0 0 1-3.168-.789 3.376 3.376 0 0 1 6.338 0Z" />
                            </svg>


                            {{ __('localize.cpr') }}: {{ $student->cpr }}
                        </p>
                        <p>

                            <svg style="width:16px" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                            </svg>


                            {{ __('localize.mobile') }}: {{ $student->mobile }}
                        </p>





                    </div>

                </div>
                {{--  --}}
                <h2>{{ __('localize.courses') }}</h2>
                @php
                    $flag_onse = true;
                @endphp
                @foreach ($student->classrooms as $classroom)
                    {{-- if not first show old courses --}}
                    @if (!$loop->first and $flag_onse)
                        @php
                            $flag_onse = false;
                        @endphp
                        <b>{{ __('localize.old_courses') }}</b>
                    @endif


                    <div class="card mb-3">
                        {{-- ##################################### --}}
                        {{--  course name --}}
                        {{-- ##################################### --}}
                        <div class="card-header">
                            <b>{{ $classroom->course->long_name }}</b>
                        </div>


                        {{-- ##################################### --}}
                        {{--  time table --}}
                        {{-- ##################################### --}}
                        <div class="card-header" data-bs-toggle="collapse"
                            data-bs-target="#courseDetails{{ $classroom->id }}" aria-expanded="false"
                            aria-controls="courseDetails{{ $classroom->id }}">


                            <svg style="width:16px" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                            </svg>



                            {{ __('localize.view_course_details') }}
                        </div>
                        <div class="collapse" id="courseDetails{{ $classroom->id }}">
                            <div class="card-body">
                                @include('reports.student.components.student_table_template', [
                                    'classRoom' => $classroom,
                                    'course' => $classroom->course,
                                    'student' => $student,
                                ])
                            </div>
                        </div>


                        {{-- ##################################### --}}
                        {{--  payments --}}
                        {{-- ##################################### --}}
                        <div class="card-header" data-bs-toggle="collapse"
                            data-bs-target="#paymentsDetails{{ $classroom->id }}" aria-expanded="false"
                            aria-controls="paymentsDetails{{ $classroom->id }}">

                            <svg style="width:16px" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                            </svg>

                            {{ __('localize.payments') }}
                        </div>
                        <div class="collapse" id="paymentsDetails{{ $classroom->id }}">

                            @if ($payments->where('course_id', $classroom->course_id)->count() > 0)
                                @foreach ($payments->where('course_id', $classroom->course_id) as $payment)
                                    <div class="card-body">

                                        @include('reports.payment.payment_print_template', [
                                            'payment' => $payment,
                                        ])

                                    </div>
                                @endforeach
                            @else
                                <div class="card-body">
                                    <em>{{ __('localize.no_payment_found') }}</em>
                                </div>
                            @endif
                        </div>





                        {{-- ##################################### --}}
                        {{--  certificate_table --}}
                        {{-- ##################################### --}}
                        <div class="card-header" data-bs-toggle="collapse"
                            data-bs-target="#certificateDetails{{ $classroom->id }}" aria-expanded="false"
                            aria-controls="certificateDetails{{ $classroom->id }}">

                            <svg style="width:16px" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                            </svg>


                            {{ Setting::get('certificate.title') == '' ? __('reports.certificate') : Setting::get('certificate.title') }}
                        </div>
                        <div class="collapse" id="certificateDetails{{ $classroom->id }}">
                            <div class="card-body">

                                @if ($studentmarks->where('course_id', $classroom->course->id)->first())
                                    @include('reports.certificate_table', [
                                        'studentmark' => $studentmarks->where('course_id', $classroom->course->id)->first(),
                                        'table_width' => '100%',
                                    ])
                                @else
                                    <div class="card-body">
                                        <em>{{ __('localize.no_certificate_found') }}</em>
                                    </div>
                                @endif
                            </div>
                        </div>


                    </div>
                @endforeach



                {{--  --}}
            </div>
        </div>
    </div>
@endsection
