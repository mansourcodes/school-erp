@extends('layouts.' . $print)


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
                            نفيدكم علماً أنّ الطالب /
                        </b>

                        {{ $studentmark->student->name }}
                    </td>
                    <td>
                        <b>
                            الرقم الشخصي
                        </b>

                        {{ $studentmark->student->cpr }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <b>
                            الفصل الدراسي /
                        </b>

                        {{ $studentmark->course->academicPath->academic_path_name }}

                        {{ $studentmark->course->hijri_year }} ه_

                        ({{ $studentmark->course->course_year }} م)
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
                        المقرر
                    </th>
                    <th>
                        {{ __('curriculumcategory.curriculumcategory') }}

                    </th>
                    <th>
                        المدرس
                    </th>
                    <th>
                        الساعات
                    </th>
                    <th>
                        الدرجة الكلية / 100
                    </th>
                    <th>
                        الحالة
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
