@extends('layouts.' . $print)


@section('content')
    @foreach ($classRooms as $classRoom)
        @foreach ($classRoom->students as $student)
            <div class="page">

                @if (Setting::get('print_header'))
                    <img class="w-100" src="{{ URL::asset(Setting::get('print_header')) }}" />
                @endif

                <h1 class="title text-center">
                    {{ Setting::get('update_students_info.title') === '' ? __('reports.update_students_info') : Setting::get('update_students_info.title') }}
                </h1>

                {!! Setting::get('update_students_info.pre') !!}


                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>البيانات</th>
                            <th>التصحيح</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>اسم الطالب</td>
                            <td>{{ $student->student_name }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>الرقم الشخصي</td>
                            <td>{{ $student->cpr }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>النقال</td>
                            <td>{{ $student->mobile }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>الهاتف</td>
                            <td>{{ $student->mobile2 }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>المنطقة</td>
                            <td>{{ $student->address }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>الحالة الاجتماعية</td>
                            <td>مع والديه \ يتيم</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>الحالة الاقتصادية</td>
                            <td>ضعيفة \ متوسطة \ جيدة \ ممتازة</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>


                <p> هل أنت قادر على دفع رسوم الدورة ؟ (نعم \ لا) </p>

                <h4>للاستخدام الرسمي:</h4>
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>معلومات الطالب</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>رقم الطالب في المركز</td>
                            <td>{{ $student->student_id }}</td>
                        </tr>
                        <tr>
                            <td>الصف</td>
                            <td>{{ $classRoom->long_name }}</td>
                        </tr>
                        <tr>
                            <td>تم تحديث البيانات بواسطة</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>



                {!! Setting::get('update_students_info.pro') !!}

            </div>
            <div class="new-page"></div>
        @endforeach
    @endforeach
@endsection
