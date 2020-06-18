@extends('layouts.app')
<style>
    .select2-container .select2-selection--single {
        height: 34px !important;
    }

</style>
@section('content')
    <div class="content-wrapper" style="min-height: 946px;">
        <!-- Content Header (Page header) -->

        <section class="content">
            @include ('error')
            <div class="box box-info">
            <!-- Get  Attendance Log  Form  -->
            <div class="row">
                <!-- right column -->
                <div class="col-md-6">
                    <!-- Horizontal Form -->

                        <div class="box-header with-border">
                            <h3 class="box-title">ATTENDANCE</h3>
                            <?php if(!empty($schoolname)) echo $schoolname; else echo "none"; ?>
                        </div>
                        <!-- /.box-header -->
                        <!-- form start -->

                        {!! Form::open(['url' => url('attendance'), 'method' => 'get', 'class' => 'form-horizontal','files'=>true]) !!}
                        <div class="box-body">

                            @if(Auth::user()->role=="admin")
                                <div class="form-group{{ $errors->has('school_id') ? ' has-error' : '' }}">
                                    <label class="col-sm-3 control-label" for="school_id">School Id <span class="text-red">*</span></label>
                                    <div class="col-sm-9">
                                        {!! Form::select('school_id',[''=>'Please Select']+$school_name,!empty(\Illuminate\Support\Facades\Input::get('school_id'))?\Illuminate\Support\Facades\Input::get('school_id'):null,['id'=>'school_id','class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%','onchange'=> 'getClasses(this.value);']) !!}

                                        @if ($errors->has('school_id'))
                                            <span class="help-block">
                                <strong>{{ $errors->first('school_id') }}</strong>
                                </span>
                                        @endif
                                    </div>
                                </div>
                            @else
                                {!! Form::hidden('school_id', Auth::user()->school_id) !!}
                            @endif

                            <div class="form-group{{ $errors->has('class_name') ? ' has-error' : '' }}">
                                <label class="col-sm-3 control-label" for="student_id">Class Name <span class="text-red">*</span></label>
                                <div class="col-sm-9">
                                    {!! Form::select('class_name',$all_class,!empty(\Illuminate\Support\Facades\Input::get('class_name'))?\Illuminate\Support\Facades\Input::get('class_name'):null,['id' => 'class_id','class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%','onchange'=> 'get_division(this.value);']) !!}
                                    @if ($errors->has('class_name'))
                                        <span class="help-block">
                           <strong>{{ $errors->first('class_name') }}</strong>
                           </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('class_division') ? ' has-error' : '' }}">
                                <label class="col-sm-3 control-label" for="class_division">Class Division <span class="text-red">*</span></label>
                                <div class="col-sm-9">
                                    {!! Form::select('class_division',$all_division,!empty(\Illuminate\Support\Facades\Input::get('class_division'))?\Illuminate\Support\Facades\Input::get('class_division'):null,['id' => 'class_division','class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%']) !!}

                                    @if ($errors->has('class_division'))
                                        <span class="help-block">
                                     <strong>{{ $errors->first('class_division') }}</strong>
                                 </span>
                                    @endif
                                </div>
                            </div>

                                <div class="form-group{{ $errors->has('date') ? ' has-error' : '' }}">
                                    <label class="col-sm-3 control-label" for="date">Date <span class="text-red">*</span></label>
                                    <div class="col-sm-9">
                                        <div class="input-group date">
                                            {!! Form::text('date',!empty(\Illuminate\Support\Facades\Input::get('date'))?\Illuminate\Support\Facades\Input::get('date'):\Carbon\Carbon::today()->format('Y-m-d'),['class' => 'form-control', 'placeholder' => '','id'=>'datepicker']) !!}
                                            {{--{!! Form::text('date',null,['class' => 'form-control', 'placeholder' => '','id'=>'datepicker']) !!}--}}
                                            <div class="input-group-addon">
                                                <i class="fa fa-calendar"></i>
                                            </div>
                                        </div>
                                    @if ($errors->has('date'))
                                          <span class="help-block">
                                        <strong>{{ $errors->first('date') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>
                             <input type="hidden" name="showlog" value="0" id="showlog">
                        </div>
                        <!-- /.box-body -->

                        <div class="box-footer">
                            <button class="btn btn-info pull-left"  value="submit" name="submit" type="submit">Submit</button>
                            <button id="viewbtn" class="btn btn-info pull-right" name="view_log" type="button" onclick="view_hide_log()">View Log</button>
                            <button id="hidebtn" style="display:none;" class="btn btn-info pull-right" name="view_log" type="button" onclick="view_hide_log()">Hide Log</button>
                            &nbsp;&nbsp;<a href="{{ url('/attendance') }}" class="btn btn-info">Clear</a>

                        </div>
                        <!-- /.box-footer -->
                        {!! Form::close() !!}
                      </div>
                <div class="col-md-6">
                    <!-- Attendance Log -->
                        <div class="box-header with-border">
                            <h3 class="box-title">ATTENDANCE Log</h3>
                        </div>
                        <div class="box-body">
                            <div id="orders-container">

                                <div class="order-body">
                                    <table class="table table-bordered">
                                        <tbody>
                                        <tr>
                                            <th>Total Students : {{$total_student}}</th>

                                        </tr>
                                        <tr>
                                            <th>Present : {{$present}}</th>

                                        </tr>
                                        <tr>
                                            <th>Absent : {{$absent}}</th>

                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="order-footer">


                                </div>
                            </div>
                        </div>
                    <!-- Attendance Log End  -->
                </div>
                </div>

            </div>

            <!-- Get  Attendance Log  Form End -->


            <!-- View List Attendance Log  -->

            <div class="box" id="loglist" style="display:none;">

                <div class="box-body">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>School Name</th>
                            <th>Student Name</th>
                            <th>Class Name</th>
                            <th>Division</th>
                            <th>In-Time</th>
                            <th>Out-Time</th>
                            <th>Attendance Date</th>
                            <th>Attendance Time</th>
                            <th>Staff Name</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($student as  $list)
                            <?php
                            $check_present = in_array($list['id'],$present_list);
                            $check_absent = in_array($list['id'],$absent_list);
                            ?>

                            @if($check_present)
                                <?php
                                $attendance = \App\Attendance::where('student_id',$list->id)->where('attendance_date',$date)->first();
                                ?>
                            <tr>
                                <td>{{ $attendance['school']['name'] }}</td>
                                <td>{{ $attendance['student']['name'] }}</td>
                                <td>{{ $attendance['Class_Master']['name'] }}</td>
                                <td>{{ $attendance['class_division'] }}</td>
                                <td>{{ $attendance['school_in_time'] }}</td>
                                <td>{{ $attendance['school_out_time'] }}</td>
                                <td>{{ $attendance['attendance_date'] }}</td>
                                <td>{{ $attendance['attendance_time'] }}</td>
                                <td>{{ $attendance['staff_name'] }}</td>
                                <td style="padding-top: 15px;"><span class="label label-success">Present</span></td>
                                <td>
                                    <div class="btn-group-horizontal">
                                        <button class="btn btn-danger" type="button" data-toggle="modal" data-target="#myModal{{$attendance['id']}}">Absent</button>

                                        {{ Form::open(array('route' => array('attendance.show', $attendance['id']), 'method' => 'get','style'=>'display:inline')) }}
                                        <button class="btn btn-info" type="submit" ><i class="fa fa-eye"></i></button>
                                        {{ Form::close() }}

                                        {{ Form::open(array('route' => array('attendance.edit', $attendance['id']), 'method' => 'get','style'=>'display:inline')) }}
                                        <button class="btn btn-success" type="submit" ><i class="fa fa-edit"></i></button>
                                        {{ Form::close() }}

                                    </div>
                                </td>
                            </tr>
                            <div id="myModal{{$attendance['id']}}" class="fade modal modal-danger" role="dialog" >
                                {{ Form::open(array('route' => array('attendance.destroy', $attendance['id']), 'method' => 'delete','style'=>'display:inline')) }}
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span></button>
                                            <h4 class="modal-title">Delete Attendance</h4>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to delete Attendance ?</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-outline">Delete</button>
                                        </div>
                                    </div>
                                    <!-- /.modal-content -->
                                </div>
                                {{ Form::close() }}
                            </div>
                            @endif
                            @if($check_absent)
                                <tr>
                                    <td>{{ $schoolname }}</td>
                                    <td>{{ $list['name'] }}</td>
                                    <td>{{ $list['Class_Master']['name'] }}</td>
                                    <td>{{ $list['division'] }}</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td style="padding-top: 15px;"><span class="label label-danger">Absent</span></td>
                                    <td>  <button  class="btn btn-success" type="button" data-toggle="modal" data-target="#myModal1{{$list['id']}}">Present</button>

                                    </td>
                                </tr>
                                <div id="myModal1{{$list['id']}}" class="fade modal modal-default" role="dialog">
                                    <?php $student_data = \App\Student::where('id',$list['id'])->first(); ?>
                                        {!! Form::open(['url' => url('attendance'), 'method' => 'post', 'class' => 'form-horizontal','style'=>'display:inline','files'=>true]) !!}
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span></button>
                                                <h4 class="modal-title">Add Attendance</h4>
                                            </div>
                                            <div class="modal-body">

                                                    {!! Form::hidden('school_id',$student_data->school_id) !!}
                                                    {!! Form::hidden('class_name',$student_data->class_id) !!}
                                                    {!! Form::hidden('class_division',$student_data->division) !!}
                                                    {!! Form::hidden('student_id',$student_data->id) !!}
                                                    {!! Form::hidden('attendance_date',$date) !!}
                                                    {!! Form::hidden('status','active') !!}


                                                {{--<div class="form-group{{ $errors->has('class_name') ? ' has-error' : '' }}">--}}
                                                    {{--<label class="col-sm-4 control-label" for="student_id">Class Name <span class="text-red">*</span></label>--}}
                                                    {{--<div class="col-sm-8">--}}
                                                        {{--{!! Form::select('class_name',isset($modes_class_name)? $modes_class_name: [],null,['id' => 'class_id','class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%','onchange'=> 'get_division(this.value);']) !!}--}}
                                                        {{--@if ($errors->has('class_name'))--}}
                                                           {{--<span class="help-block">--}}
                                                            {{--<strong>{{ $errors->first('class_name') }}</strong>--}}
                                                        {{--</span>--}}
                                                                {{--@endif--}}
                                                    {{--</div>--}}
                                                {{--</div>--}}

                                                {{--<div class="form-group{{ $errors->has('class_division') ? ' has-error' : '' }}">--}}
                                                    {{--<label class="col-sm-4 control-label" for="class_division">Class Division <span class="text-red">*</span></label>--}}
                                                    {{--<div class="col-sm-8">--}}
                                                        {{--{!! Form::select('class_division',isset($modes_class_division)? $modes_class_division: [],null,['id' => 'class_division','class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%']) !!}--}}

                                                        {{--@if ($errors->has('class_division'))--}}
                                                            {{--<span class="help-block">--}}
                                                            {{--<strong>{{ $errors->first('class_division') }}</strong>--}}
                                                        {{--</span>--}}
                                                        {{--@endif--}}
                                                    {{--</div>--}}
                                                {{--</div>--}}

                                                {{--<div class="form-group{{ $errors->has('student_id') ? ' has-error' : '' }}">--}}
                                                    {{--<label class="col-sm-4 control-label" for="student_id">Student Id <span class="text-red">*</span></label>--}}
                                                    {{--<div class="col-sm-8">--}}
                                                        {{--{!! Form::select('student_id',isset($modes_student)? $modes_student: [],null,['id' => 'student_id','class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%']) !!}--}}
                                                        {{--@if ($errors->has('student_id'))--}}
                                                            {{--<span class="help-block">--}}
                                                            {{--<strong>{{ $errors->first('student_id') }}</strong>--}}
                                                        {{--</span>--}}
                                                        {{--@endif--}}
                                                    {{--</div>--}}
                                                {{--</div>--}}


                                                <div class="form-group{{ $errors->has('school_in_time') ? ' has-error' : '' }} ">
                                                    <label class="col-sm-4 control-label" for="school_in_time">School IN Time <span class="text-red">*</span></label>
                                                    <div class="col-sm-8">
                                                        <div class="input-group bootstrap-timepicker ">
                                                            {!! Form::text('school_in_time', null, ['class' => 'form-control pull-right timepicker', 'placeholder' => 'Start Time']) !!}
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-clock-o"></i>
                                                            </div>
                                                        </div>
                                                        @if ($errors->has('school_in_time'))
                                                            <span class="help-block">
                                                            <strong>{{ $errors->first('school_in_time') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                </div>


                                                <div class="form-group{{ $errors->has('school_out_time') ? ' has-error' : '' }} ">
                                                    <label class="col-sm-4 control-label" for="start_time">School Out Time <span class="text-red">*</span></label>
                                                    <div class="col-sm-8">
                                                        <div class="input-group bootstrap-timepicker ">
                                                            {!! Form::text('school_out_time', null, ['class' => 'form-control pull-right timepicker', 'placeholder' => 'End Time']) !!}
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-clock-o"></i>
                                                            </div>
                                                        </div>
                                                        @if ($errors->has('school_out_time'))
                                                            <span class="help-block">
                                                            <strong>{{ $errors->first('school_out_time') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                </div>

                                                {{--<div class="form-group{{ $errors->has('attendance_date') ? ' has-error' : '' }}">--}}
                                                    {{--<label class="col-sm-4 control-label" for="attendance_date">Attendance Date <span class="text-red">*</span></label>--}}
                                                    {{--<div class="col-sm-8">--}}
                                                        {{--{!! Form::text('attendance_date', null, ['class' => 'form-control', 'placeholder' => '','id'=>'datepicker001']) !!}--}}
                                                        {{--@if ($errors->has('attendance_date'))--}}
                                                            {{--<span class="help-block">--}}
                                                            {{--<strong>{{ $errors->first('attendance_date') }}</strong>--}}
                                                        {{--</span>--}}
                                                        {{--@endif--}}
                                                    {{--</div>--}}
                                                {{--</div>--}}

                                                <div class="form-group{{ $errors->has('attendance_time') ? ' has-error' : '' }} ">
                                                    <label class="col-sm-4 control-label" for="start_time">Attendance Time <span class="text-red">*</span></label>
                                                    <div class="col-sm-8">
                                                        <div class="input-group bootstrap-timepicker ">
                                                            {!! Form::text('attendance_time', null, ['class' => 'form-control pull-right timepicker', 'placeholder' => 'End Time']) !!}
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-clock-o"></i>
                                                            </div>
                                                        </div>
                                                        @if ($errors->has('attendance_time'))
                                                                <span class="help-block">
                                                            <strong>{{ $errors->first('attendance_time') }}</strong>
                                                        </span>
                                                        @endif
                                                    </div>
                                                </div>
                                               {{--<br>--}}
                                                {{--<div class="form-group{{ $errors->has('status') ? ' has-error' : '' }}">--}}
                                                    {{--<label class="col-sm-4 control-label" for="role">Status <span class="text-red">*</span></label>--}}

                                                    {{--<div class="col-sm-8">--}}

                                                        {{--@foreach (\App\School::$status as $key => $value)--}}
                                                            {{--<label>--}}
                                                                {{--{!! Form::radio('status', $key, ($key=='active') ? true:false, ['class' => 'flat-red']) !!} <span style="margin-right: 10px">{{ $value }}</span>--}}
                                                            {{--</label>--}}
                                                        {{--@endforeach--}}

                                                        {{--@if ($errors->has('status'))--}}
                                                            {{--<span class="help-block">--}}
                                                             {{--<strong>{{ $errors->first('status') }}</strong>--}}
                                                            {{--</span>--}}
                                                        {{--@endif--}}
                                                    {{--</div>--}}
                                                {{--</div>--}}
                                            </div>
                                             <br><br><br><br><br>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-fefault pull-left" data-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">Add</button>
                                            </div>
                                        </div>
                                    </div>
                                    {{ Form::close() }}
                                </div>
                            @endif
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- View List Attendance Log End  -->

        </section>
    </div>

@endsection




<script type="text/javascript">

    function view_hide_log(){

        var show=document.getElementById('showlog').value;

        if(show == '0') {
            document.getElementById('loglist').style.display = "block";
            document.getElementById('viewbtn').style.display = "none";
            document.getElementById('hidebtn').style.display = "block";
            document.getElementById('showlog').value = '1';
        }
        else{

            document.getElementById('loglist').style.display = "none";
            document.getElementById('viewbtn').style.display = "block";
            document.getElementById('hidebtn').style.display = "none";
            document.getElementById('showlog').value=0;
        }
    }
    function getClasses(val){

        $.ajax({
            url: '{{ url('attendance/get_classes') }}/'+val,
            async: false,
            error:function(){
            },
            success: function(result){

                $("#class_id").select2().empty();
                $("#class_id").html(result);
                $('#class_id').select2();
            }
        });
        var class_id=document.getElementById('class_id').value;
        get_division(class_id);
    }

    function get_division(class_id){
        var school_id=document.getElementById('school_id').value;
        $.ajax({
            url: '{{ url('attendance/get_division') }}/'+class_id +'/'+school_id,
            error:function(){
            },
            success: function(result){
                // alert(result);
                $("#class_division").select2().empty();
                $("#class_division").html(result);
                $('#class_division').select2()
            }
        });
    }

</script>
