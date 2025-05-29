@extends('layouts.' . $print)


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

                        {{ $attend_curriculum_table['start_time']->format('h:m') }}
                        {{ __('base.' . $attend_curriculum_table['start_time']->format('a')) }}
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
                @foreach ($classroom->students as $key => $student)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <th class="onlinecontent text-right">{{ $student->name }}</th>
                        @foreach ($attend_curriculum_table['calander_days'] as $date)
                            <td></td>
                        @endforeach

                    </tr>
                @endforeach

            </table>


            {!! Setting::get('student_attend_list.pro') !!}

        </div>
        @if (!$loop->last)
            <div class="new-page"></div>
        @endif
    @endforeach
@endsection
