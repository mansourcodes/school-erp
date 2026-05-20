@extends('backend.layouts.' . $print)


@section('content')
    @foreach ($course_id as $id => $student_data)
        <div class="page">

            @if (Setting::get('print_header'))
                <img class="w-100" src="{{ URL::asset(Setting::get('print_header')) }}" />
            @endif

            <h1 class="title text-center">
                {{ Setting::get('student_courses_transcript.title') === '' ? __('reports.student_courses_transcript') : Setting::get('student_courses_transcript.title') }}
            </h1>





            <table class="table table-no-border ">
                <tr>
                    <td>
                        <b>
                            Ù†ÙÙŠØ¯ÙƒÙ… Ø¹Ù„Ù…Ø§Ù‹ Ø£Ù†Ù‘ Ø§Ù„Ø·Ø§Ù„Ø¨ /
                        </b>

                        {{ $student_data['studentmarks']->student->name }}
                    </td>
                    <td>
                        <b>
                            Ø§Ù„Ø±Ù‚Ù… Ø§Ù„Ø´Ø®ØµÙŠ
                        </b>

                        {{ $student_data['studentmarks']->student->cpr }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <b>
                            Ø§Ù„ÙØµÙ„ Ø§Ù„Ø¯Ø±Ø§Ø³ÙŠ /
                        </b>

                        {{ $student_data['studentmarks']->course->academicPath->academic_path_name }}

                        {{ $student_data['studentmarks']->course->hijri_year }} Ù‡_

                        ({{ $student_data['studentmarks']->course->course_year }} Ù…)
                        {{ $student_data['studentmarks']->course->semester }}
                        -
                        {{ $student_data['studentmarks']->course->academicPath->academic_path_type }}



                    </td>
                    <td>

                    </td>
                </tr>
            </table>

            {!! Setting::get('student_courses_transcript.pre') !!}


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
                        Ù…Ø¬Ù…ÙˆØ¹ Ø§Ù„Ø§Ø¹Ù…Ø§Ù„
                    </th>
                    <th>
                        Ø¥Ø®ØªØ¨Ø§Ø± Ø§Ù„Ù…Ù†ØªØµÙ
                    </th>
                    <th>
                        Ø§Ù„Ø§Ø®ØªØ¨Ø§Ø± Ø§Ù„Ù†Ù‡Ø§Ø¦ÙŠ
                    </th>
                    <th>
                        Ø§Ù„Ø­Ø¶ÙˆØ± ÙˆØ§Ù„Ø§Ù†Ø¶Ø¨Ø§Ø·
                    </th>
                    <th>
                        Ø§Ù„Ø¯Ø±Ø¬Ø© Ø§Ù„ÙƒÙ„ÙŠØ© / 100
                    </th>
                    <th>
                        Ø§Ù„Ø­Ø§Ù„Ø©
                    </th>
                </tr>
                <?php $counter = 0; ?>
                @foreach ($student_data['studentmarks']->marks as $subject_mark)
                    <tr>
                        <th scope="row">{{ ++$counter }}</th>

                        <td>
                            {{ $student_data['curriculums'][$subject_mark['curriculum_id']]->curriculum_name }}
                            -
                            {{ $student_data['curriculums'][$subject_mark['curriculum_id']]->book_name }}
                        </td>
                        <td>
                            {{ $student_data['curriculums'][$subject_mark['curriculum_id']]->curriculumCategory->category_name ?? '-' }}
                        </td>

                        <td>
                            {{ $student_data['curriculums'][$subject_mark['curriculum_id']]->teacher_name }}

                        </td>
                        <td>
                            {{ $student_data['curriculums'][$subject_mark['curriculum_id']]->weight_in_hours }}
                        </td>


                        <td>
                            {{ $subject_mark['class_mark_details'][0]->mark ?? '' }}
                        </td>

                        <td>
                            {{ $subject_mark['midexam_marks_details'][0]->mark ?? '' }}
                        </td>

                        <td>
                            {{ $subject_mark['finalexam_mark_details'][0]->mark ?? '' }}
                        </td>

                        <td>
                            {{ $subject_mark['attend_mark_details'][0]->mark ?? '' }}
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

            {!! Setting::get('student_courses_transcript.pro') !!}

        </div>

        @if (!$loop->last)
            <div class="new-page"></div>
        @endif
    @endforeach
@endsection
