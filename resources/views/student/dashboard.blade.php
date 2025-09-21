@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">

                {{--  --}}
                <h2>{{ __('localize.student_dashboard') }}</h2>

                <div class="card mb-5">

                    <div class="card-header">
                        {{ __('localize.welcome') }}, {{ $student->name }}
                    </div>


                    <div class="card-body">

                        <ul>
                            <li>{{ __('localize.student_id') }}: {{ $student->student_id }}</li>
                            <li>{{ __('localize.cpr') }}: {{ $student->cpr }}</li>
                            <li>{{ __('localize.mobile') }}: {{ $student->mobile }}</li>
                        </ul>




                    </div>

                </div>
                {{--  --}}
                <h2>{{ __('localize.courses') }}</h2>
                @foreach ($student->classrooms as $classroom)
                    {{-- if not first show old courses --}}
                    @if (!$loop->first)
                        <b>{{ __('localize.old_courses') }}</b>
                    @endif


                    <div class="card mb-3">
                        <div class="card-header">
                            {{ $classroom->course->long_name }}
                        </div>
                        <div class="card-header" data-bs-toggle="collapse"
                            data-bs-target="#courseDetails{{ $classroom->id }}" aria-expanded="false"
                            aria-controls="courseDetails{{ $classroom->id }}">
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


                        <div class="card-header" data-bs-toggle="collapse"
                            data-bs-target="#paymentsDetails{{ $classroom->id }}" aria-expanded="false"
                            aria-controls="paymentsDetails{{ $classroom->id }}">
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
                    </div>
                @endforeach



                {{--  --}}
            </div>
        </div>
    </div>
@endsection
