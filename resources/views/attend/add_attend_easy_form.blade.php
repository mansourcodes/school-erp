<!-- s -->
@extends(backpack_view('blank'))




@section('content')
<form>


    <div class="container-fluid">
        <h2>
            <span class="text-capitalize">
                {{__('attend.add_attend_w_date')}}
            </span>
        </h2>


        <div class=" col-10">
            <div class="card ">
                <div class="card-header"><strong>{{__('attend.add_attend_w_date')}}</strong></div>
                <div class="card-body">
                    <!-- /.row-->
                    <div class="row">
                        <div class="form-group col-4">
                            <label>{{__('curriculum.curriculum')}} </label>
                            <input type="hidden" name="date" value="{{$curriculum->id}}" />
                            <div class="form-control">{{$curriculum->long_name}} </div>
                        </div>
                        <div class="form-group col-4">
                            <label>{{__('base.date')}} </label>
                            <input type="hidden" name="date" value="{{$date->format('Y-m-d')}}" />
                            <div class="form-control">{{$date->locale('ar')->dayName . $date->format(' d ') . $date->locale('ar')->monthName . $date->format(' Y')}}</div>
                        </div>
                        <div class="form-group col-4">
                            <label>{{__('classroom.start_time')}} </label>
                            <input type="hidden" name="start_time" value="{{$start_time->format('H:m')}}" />
                            <div class="form-control">{{$start_time->format('H:m')}}</div>
                        </div>
                    </div>
                    <!-- /.row-->
                    <!-- /.row-->
                    <div class="row">
                        <div class="form-group col-6">
                            <label>{{__('classroom.classroom')}} </label>
                            <input type="hidden" name="date" value="{{$classroom->id}}" />
                            <div class="form-control">{{$classroom->long_name}}</div>
                        </div>
                        <div class="form-group col-6">
                            <label>{{__('course.course')}} </label>
                            <input type="hidden" name="date" value="{{$classroom->course->id}}" />
                            <div class="form-control">{{$classroom->course->long_name}}</div>
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
                    </tr>
                    @endforeach


                </tbody>
            </table>
        </div>

    </div>

</form>

@endsection


@section('after_scripts')

<script>

</script>

@endsection
