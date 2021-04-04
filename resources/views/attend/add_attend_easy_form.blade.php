<!-- s -->
@extends(backpack_view('blank'))




@section('content')
<form action="{{ backpack_url('save_attend_easy_form') }}">


    <div class="__container-fluid">
        <h2>
            <span class="text-capitalize">
                {{__('attend.add_attend_w_date')}}
            </span>
        </h2>


        <div class="col">
            <div class="card ">
                <div class="card-header"><strong>{{__('attend.add_attend_w_date')}}</strong></div>
                <div class="card-body">
                    <!-- /.row-->
                    <div class="row">
                        <div class="form-group col-12 col-md-4">
                            <label>{{__('curriculum.curriculum')}} </label>
                            <div class="form-control h-auto">{{$curriculum->long_name}} </div>
                        </div>
                        <div class="form-group col-6 col-md-4">
                            <label>{{__('base.date')}} </label>
                            <div class="form-control h-auto">{{$date->locale('ar')->dayName . $date->format(' d ') . $date->locale('ar')->monthName . $date->format(' Y')}}</div>
                        </div>
                        <div class="form-group col-6 col-md-4">
                            <label>{{__('classroom.start_time')}} </label>
                            <div class="form-control h-auto">{{$start_time->format('H:m')}} {{__('base.'.$start_time->format('a'))}}
                            </div>
                        </div>

                        <div class="form-group col-6 col-md-6">
                            <label>{{__('classroom.classroom')}} </label>
                            <div class="form-control h-auto">{{$classroom->long_name}}</div>
                        </div>
                        <div class="form-group col-6 col-md-6">
                            <label>{{__('course.course')}} </label>
                            <div class="form-control h-auto">{{$classroom->course->long_name}}</div>
                        </div>
                    </div>
                    <!-- /.row-->
                </div>
            </div>


            <table class="table table-responsive-sm table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{__('student.student_name')}}</th>
                        <th>{{__('attend.attend_students')}}


                            <button type="button" class="btn   btn-success float-left" onclick="allAttend()">
                                <i class="las la-fill-drip"></i>
                            </button>

                        </th>
                        <th>{{__('attend.late_students')}}

                            <button type="button" class="btn  float-left" onclick="allLateWithExcuse()">
                                <i class="las la-fill-drip"></i>
                            </button>
                            <button type="button" class="btn   btn-warning float-left" onclick="allLate()">
                                <i class="las la-fill-drip"></i>
                            </button>
                        </th>
                        <th>{{__('attend.absent_students')}}

                            <button type="button" class="btn  float-left" onclick="allAbsentWithExcuse()">
                                <i class="las la-fill-drip"></i>
                            </button>
                            <button type="button" class="btn   btn-dark float-left" onclick="allAbsent()">
                                <i class="las la-fill-drip"></i>
                            </button>


                        </th>

                    </tr>
                </thead>
                <tbody>

                    @foreach ($classroom->students as $key => $student)
                    <tr>
                        <td>
                            {{$key+1}}
                        </td>
                        <td>
                            {{$student->student_name}}
                        </td>

                        <td>
                            <label class="switch attend-switch switch-pill switch-success">
                                <input class="switch-input" type="radio" value="attend" name="student_attend_state[{{$student->id}}]" checked><span class="switch-slider">{{__('attend.student_attended')}}</span>
                            </label>
                        </td>


                        <td>
                            <label class="switch attend-switch switch-pill switch-late">
                                <input class="switch-input" type="radio" value="late" name="student_attend_state[{{$student->id}}]"><span class="switch-slider">{{__('attend.student_late')}}</span>
                            </label>
                            <label class="switch attend-switch switch-pill switch-late_w_excuse">
                                <input class="switch-input" type="radio" value="late_w_excuse" name="student_attend_state[{{$student->id}}]"><span class="switch-slider">{{__('attend.student_late_w_excuse')}}</span>
                            </label>
                        </td>


                        <td>
                            <label class="switch attend-switch switch-pill switch-absent">
                                <input class="switch-input" type="radio" value="absent" name="student_attend_state[{{$student->id}}]"><span class="switch-slider">{{__('attend.student_absent')}}</span>
                            </label>
                            <label class="switch attend-switch switch-pill switch-absent_w_excuse">
                                <input class="switch-input" type="radio" value="absent_w_excuse" name="student_attend_state[{{$student->id}}]"><span class="switch-slider">{{__('attend.student_absent_w_excuse')}}</span>
                            </label>
                        </td>

                    </tr>
                    @endforeach


                </tbody>
            </table>

            <button class="btn btn-lg  btn-block btn-primary" type="submit">
                {{__('attend.add_attends')}}
            </button>



        </div>

    </div>




    <input type="hidden" name="date" value="{{$date->format('Y-m-d')}}" />
    <input type="hidden" name="start_time" value="{{$start_time->format('H:m')}}" />
    <input type="hidden" name="curriculum_id" value="{{$curriculum->id}}" />
    <input type="hidden" name="class_room_id" value="{{$classroom->id}}" />
    <input type="hidden" name="course_id" value="{{$classroom->course->id}}" />

</form>

@endsection


@section('after_scripts')

<script>
    function allAttend() {
        $('.switch-success').click();
    }

    function allLate() {
        $('.switch-late').click();
    }

    function allLateWithExcuse() {
        $('.switch-late_w_excuse').click();
    }

    function allAbsent() {
        $('.switch-absent').click();
    }

    function allAbsentWithExcuse() {
        $('.switch-absent_w_excuse').click();
    }
</script>

@endsection
