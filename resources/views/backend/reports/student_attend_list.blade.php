@extends('backend.layouts.' . $print)


@section('content')
    <style>
        @media print {
            @page {
                size: landscape
            }
        }

        th.rotate {
            width: 60px;
            height: 130px;
            white-space: nowrap;
            position: relative;
            overflow: hidden;
        }

        table.table.fixed-layout {
            /* table-layout: fixed; */

        }

        th.rotate>p {
            transform: rotate(90deg);
            position: absolute;
            left: 0;
            top: 50px;
            margin: auto;
            width: -webkit-fill-available;
            height: 23px;
        }

        tr th.onlinecontent,
        tr td.onlinecontent {
            width: 1%;
            white-space: nowrap;
        }
    </style>

    @php
        $state_marks = [
            'attend' => ['mark' => '✓', 'class' => 'text-success'],
            'late' => ['mark' => 'ت', 'class' => 'text-primary'],
            'late_w_excuse' => ['mark' => 'تع', 'class' => 'text-primary'],
            'absent' => ['mark' => '✗', 'class' => 'text-danger'],
            'absent_w_excuse' => ['mark' => 'غع', 'class' => 'text-danger'],
        ];
    @endphp

    @foreach ($attend_table as $attend_curriculum_table)
        <div class="page">


            @if (Setting::get('print_header'))
                <img class="w-100" src="{{ URL::asset(Setting::get('print_header')) }}" />
            @endif

            <h1 class="title text-center">
                {{ Setting::get('student_attend_list.title') === '' ? __('reports.student_attend_list') : Setting::get('student_attend_list.title') }}
            </h1>




            {!! Setting::get('student_attend_list.pre') !!}


            <table class="table fixed-layout  ">
                <tr>
                    <td colspan="2">
                        <b>
                            {{ __('curriculum.curriculum') }}:
                        </b>

                        {{ $attend_curriculum_table['curriculum']->curriculum_name }}
                    </td>
                    <td>
                        <b>
                            {{ $attend_curriculum_table['day_name'] }}
                        </b>

                        @if ($attend_curriculum_table['start_time'])
                            {{ $attend_curriculum_table['start_time']->format('h:i') }}
                            {{ __('base.' . $attend_curriculum_table['start_time']->format('a')) }}
                        @endif
                    </td>
                    <td>
                        <b>
                            المعلم:
                        </b>
                        {{ @$teachers[$attend_curriculum_table['curriculum']->id] }}
                    </td>
                    <td>
                        <b>
                            الصف:
                            {{ $classroom->first_long_name }}

                        </b>

                    </td>
                </tr>
            </table>



            <table class="table  table-ziped text-center">
                <tr>
                    <th style="width: 1%;" class="align-middle">#</th>
                    <th class="align-middle">الاسم</th>
                    @foreach ($attend_curriculum_table['calander_days'] as $date)
                        <th class="rotate">
                            <p>{{ $date }}</p>
                        </th>
                    @endforeach
                </tr>
                @php
                    $slot_hour = $attend_curriculum_table['start_time'] ? $attend_curriculum_table['start_time']->format('H') : null;
                @endphp
                @foreach ($classroom->students as $key => $student)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <th class="onlinecontent text-right">{{ $student->name }}</th>
                        @foreach ($attend_curriculum_table['calander_days'] as $date)
                            @php
                                [$state, $from_other_classroom] = $attendance->stateFor(
                                    $attend_curriculum_table['curriculum']->id, $date, $slot_hour, $student->id
                                );
                            @endphp
                            <td class="align-middle {{ $state ? $state_marks[$state]['class'] : '' }}"
                                title="{{ $state ? __('attend.student_' . ($state == 'attend' ? 'attended' : $state)) : '' }}{{ $from_other_classroom ? ' (صف آخر)' : '' }}">
                                {{ $state ? $state_marks[$state]['mark'] : '' }}{{ $from_other_classroom ? '*' : '' }}
                            </td>
                        @endforeach

                    </tr>
                @endforeach

            </table>

            <p class="small">
                @foreach ($state_marks as $state => $state_mark)
                    <span class="mr-3 ml-1 {{ $state_mark['class'] }}"><b>{{ $state_mark['mark'] }}</b></span>
                    {{ __('attend.student_' . ($state == 'attend' ? 'attended' : $state)) }}
                @endforeach
                <span class="mr-3 ml-1"><b>*</b></span>
                مسجل في صف آخر من نفس الدورة
            </p>


            {!! Setting::get('student_attend_list.pro') !!}

        </div>
        @if (!$loop->last)
            <div class="new-page"></div>
        @endif
    @endforeach
@endsection
