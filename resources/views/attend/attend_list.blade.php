<!-- s -->
@extends(backpack_view('blank'))




@section('content')
<div class="container-fluid">
    <h2>
        <span class="text-capitalize">
            {{__('attend.add_attend_w_date')}}
        </span>
    </h2>


    <div class="col-6 ">
        <div class="card ">
            <div class="card-header">
                <strong>
                    {{__('attend.add_attend_w_date')}}
                </strong>
            </div>
            <div class="card-body">
                <form>
                    <!-- /.row-->
                    <div class="row">
                        <div class="form-group col-6">
                            <label for="ccmonth">{{__('base.date')}}</label>
                            <input name="chosen_date" class="form-control" type="date" value="{{$chosen_date}}">
                        </div>
                        <div class="form-group col-6">
                            <label>&nbsp; &nbsp; </label>
                            <button class="btn btn-block btn-primary" type="submit">
                                {{__('base.search')}}
                            </button>
                        </div>
                    </div>
                    <!-- /.row-->
            </div>
            </form>
        </div>
    </div>



    <table class="table table-responsive-sm table-bordered">
        <thead>
            <tr>
                <th>{{__('base.date')}}</th>
                <th>{{__('classroom.start_time')}}</th>
                <th>{{__('curriculum.curriculum')}}</th>
                <th>{{__('classroom.classroom')}}</th>
                <th>{{__('course.course')}}</th>
                <th>{{__('attend.add_attends')}}</th>

            </tr>
        </thead>
        <tbody>
            @foreach ($classrooms as $classroom)
            @foreach ($classroom->active_attend_table as $active_attend_table)
            <tr>
                <td>{{$chosen_date_long}}</td>
                <td>
                    {{$active_attend_table['start_time']->format('h:m')}}
                    {{__('base.'.$active_attend_table['start_time']->format('a'))}}
                </td>
                <td>{{$active_attend_table['curriculum']->long_name}}</td>
                <td>{{$classroom->long_name}}</td>
                <td>{{$classroom->course->long_name}}</td>
                <td>
                    @if($active_attend_table['is_recorded'])
                    <form action="{{ backpack_url('add_attend_easy_form') }} ">

                        <input type="hidden" name="date" value="{{$chosen_date}}" />
                        <input type="hidden" name="start_time" value="{{$active_attend_table['start_time']->format('H:m')}}" />
                        <input type="hidden" name="curriculum_id" value="{{$active_attend_table['curriculum']->id}}" />
                        <input type="hidden" name="class_room_id" value="{{$classroom->id}}" />
                        <input type="hidden" name="course_id" value="{{$classroom->course->id}}" />
                        <button class="btn  btn-sm btn-primary" type="submit">
                            {{__('attend.add_attends')}}
                        </button>
                    </form>
                    @else
                    <button class="btn  btn-sm " disabled type="button">
                        {{__('attend.added_attends')}}
                    </button>
                    @endif


                </td>
            </tr>
            @endforeach
            @endforeach

        </tbody>
    </table>

</div>


@endsection


@section('after_scripts')

<script>

</script>

@endsection
