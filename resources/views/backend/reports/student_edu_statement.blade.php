@extends('backend.layouts.' . $print)


@section('content')
    @foreach ($studentmarks as $studentmark)
        <div class="page">


            @if (Setting::get('print_header'))
                <img class="w-100" src="{{ URL::asset(Setting::get('print_header')) }}" />
            @endif

            <h1 class="title text-center">
                {{ Setting::get('student_edu_statement.title') === '' ? __('reports.student_edu_statement') : Setting::get('student_edu_statement.title') }}
            </h1>





            <table class="table table-no-border ">
                <tr>
                    <td>
                        <b>
                            Ù†ÙÙŠØ¯ÙƒÙ… Ø¹Ù„Ù…Ø§Ù‹ Ø£Ù†Ù‘ Ø§Ù„Ø·Ø§Ù„Ø¨ /
                        </b>

                        {{ $studentmark->student->name }}
                    </td>
                    <td>
                        <b>
                            Ø§Ù„Ø±Ù‚Ù… Ø§Ù„Ø´Ø®ØµÙŠ
                        </b>

                        {{ $studentmark->student->cpr }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <b>
                            Ø§Ù„ÙØµÙ„ Ø§Ù„Ø¯Ø±Ø§Ø³ÙŠ /
                        </b>

                        {{ $studentmark->course->academicPath->academic_path_name }}

                        {{ $studentmark->course->hijri_year }} Ù‡_

                        ({{ $studentmark->course->course_year }} Ù…)
                        {{ $studentmark->course->semester }}
                        -
                        {{ $studentmark->course->academicPath->academic_path_type }}



                    </td>
                    <td>

                    </td>
                </tr>
            </table>

            {!! Setting::get('student_edu_statement.pre') !!}


            <table class="table">
                <tr>
                    <th scope="row"></th>

                    <th>
                        Ø§Ù„Ù…Ù‚Ø±Ø±
                    </th>
                    <th>
                        {{ __('curriculumcategory.curriculumcategory') }}

                    </th>
                    <th>
                        Ø§Ù„Ù…Ø¯Ø±Ø³
                    </th>
                    <th>
                        Ø§Ù„Ø³Ø§Ø¹Ø§Øª
                    </th>
                    <th>
                        Ø§Ù„Ø¯Ø±Ø¬Ø© Ø§Ù„ÙƒÙ„ÙŠØ© / 100
                    </th>
                    <th>
                        Ø§Ù„Ø­Ø§Ù„Ø©
                    </th>
                </tr>
                <?php $counter = 0; ?>
                @foreach ($studentmark->marks as $subject_mark)
                    <tr>
                        <th scope="row">{{ ++$counter }}</th>

                        <td>
                            {{ $curriculums[$subject_mark['curriculum_id']]->curriculum_name }}
                            -
                            {{ $curriculums[$subject_mark['curriculum_id']]->book_name }}
                        </td>
                        <td>
                            {{ $curriculums[$subject_mark['curriculum_id']]->curriculumCategory->category_name ?? '-' }}
                        </td>

                        <td>
                            {{ $curriculums[$subject_mark['curriculum_id']]->teacher_name }}

                        </td>
                        <td>
                            {{ $curriculums[$subject_mark['curriculum_id']]->weight_in_hours }}
                        </td>


                        <td>
                            {{ $subject_mark['total_mark'] }}
                        </td>
                        <td>
                            {{ $subject_mark['final_grade'] }}
                        </td>
                    </tr>
                @endforeach

            </table>

            {!! Setting::get('student_edu_statement.pro') !!}

        </div>

        @if (!$loop->last)
            <div class="new-page"></div>
        @endif
    @endforeach
@endsection
