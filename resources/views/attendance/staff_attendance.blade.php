<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title">Staff List</h3>
        <div class="pull-right">
            <div class="form-group">
                            <span class="col-sm-9 col-xs-9">
                        <input  class="form-control" type="text" @if(!empty($search_staff)) value="{{$search_staff}}" @else placeholder="Search" @endif name="search_staff" id="search_staff">
                            </span>
                        <span class=" col-sm-3 col-xs-3">
                            <button class="btn btn-info pull-right" type="button" onclick="search_staff()">Search</button>
                        </span>
            </div>
        </div>
    </div>
    <div class="box-body table-responsive">
        <table   id="" class="table table-bordered table-striped">
            <thead>
            <tr>
                <th>School Name</th>
                <th>Staff Name</th>
                <th>In-Time</th>
                <th>Out-Time</th>
                <th>Attendance Date</th>
                <th>Attendance Time</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($staff as  $list1)

                <?php
                $check_present_staff = in_array($list1['id'],$staff_present_list);
                $check_absent_staff = in_array($list1['id'],$staff_absent_list);
                $check_leave_staff = in_array($list1['id'],$staff_leave_list);
                ?>

                @if($check_present_staff || $check_leave_staff)
                    <?php
                    $staff_attendance = \App\Attendance::where('staff_id',$list1->id)->where('attendance_date',$date)->first();
                    ?>
                    <tr>
                        <td>{{ $staff_attendance['school']['name'] }}</td>
                        <td>{{ $staff_attendance['staff_name'] }}</td>
                        <td>{{ $staff_attendance['school_in_time'] }}</td>
                        <td>{{ $staff_attendance['school_out_time'] }}</td>
                        <td>{{ $staff_attendance['attendance_date'] }}</td>
                        <td>{{ $staff_attendance['attendance_time'] }}</td>
                        @if($staff_attendance->on_leave == 1)
                            <td style="padding-top:15px;"><span class="label label-info">Leave</span></td>
                        @else
                            <td style="padding-top:15px;"><span class="label label-success">Present</span></td>
                        @endif
                           <td>
                            <div class="btn-group-horizontal">
                                <button class="btn btn-danger" type="button" data-toggle="modal" data-target="#myModal{{$staff_attendance['id']}}">Absent</button>

                                 {{Form::open(array('route' => array('attendance.show', $staff_attendance['id']), 'method' => 'get','style'=>'display:inline')) }}
                                <button class="btn btn-info" type="submit" ><i class="fa fa-eye"></i></button>
                                {{ Form::close() }}

                               {{-- {{ Form::open(array('route' => array('attendance.edit', $staff_attendance['id']), 'method' => 'get','style'=>'display:inline')) }}
                                <button class="btn btn-success" type="submit" ><i class="fa fa-edit"></i></button>
                                {{ Form::close() }}  --}}

                            </div>
                        </td>
                    </tr>
                    <div id="myModal{{$staff_attendance['id']}}" class="fade modal modal-danger" role="dialog" >
                        {{ Form::open(array('route' => array('attendance.destroy', $staff_attendance['id']), 'method' => 'delete','style'=>'display:inline')) }}
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
                @if($check_absent_staff)
                    <tr>
                        <td>{{ $schoolname }}</td>
                        <td>{{ $list1['name'] }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="padding-top: 15px;"><span class="label label-danger">Absent</span></td>
                        <td>  <button  class="btn btn-success" type="button" data-toggle="modal" data-target="#myModal1{{$list1['id']}}">Present</button>

                        </td>
                    </tr>

                    <div id="myModal1{{$list1['id']}}" class="fade modal modal-default" role="dialog">
                        <?php $staff_data = \App\Staff::where('id',$list1['id'])->first(); ?>
                        {!! Form::open(['url' => url('attendance/staff'), 'method' => 'post', 'class' => 'form-horizontal','style'=>'display:inline','files'=>true]) !!}
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title">Add Attendance</h4>
                                </div>
                                <div class="modal-body">

                                    {!! Form::hidden('school_id',$staff_data->school_id) !!}
                                    {!! Form::hidden('staff_id',$staff_data->id) !!}
                                    {!! Form::hidden('staff_name',$staff_data->name) !!}
                                    {!! Form::hidden('attendance_date',$date) !!}
                                    {!! Form::hidden('attendance_time',\Carbon\Carbon::now()->format('H:i:s')) !!}
                                    {!! Form::hidden('status','active') !!}

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
                                     <br>
                                    <div class="form-group{{ $errors->has('school_out_time') ? ' has-error' : '' }}">
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
                                    <br>
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label" for="on_leave">On Leave</label>

                                        <div class="col-sm-8">

                                            {{ Form::checkbox('on_leave',1,null,['class' => 'myCheckbox']) }}
                                        </div>
                                    </div>
                                    <br>
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label" for="notification">Notification Type<span class="text-red"></span></label>

                                        <div class="col-sm-8">
                                            <input type="radio" name="notification_type" value="notification"><label><span style="margin-right: 10px">Notification</span></label>
                                            <input type="radio" name="notification_type" value="sms"><label><span style="margin-right: 10px">SMS</span></label>
                                            <input type="radio" name="notification_type" value="both"><label><span style="margin-right: 10px">Both</span></label>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="form-group{{ $errors->has('remark') ? ' has-error' : '' }}">
                                        <label class="col-sm-4 control-label" for="remark">Remark </label>
                                        <div class="col-sm-8">
                                            {!! Form::textarea('remark', null, ['class' => 'form-control','rows'=>'4', 'placeholder' => '']) !!}
                                            @if ($errors->has('remark'))
                                                <span class="help-block">
                                                            <strong>{{ $errors->first('remark') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                </div>
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
        @if(count($staff))
        <div style="text-align:right;float:right;"> @include('pagination.limit_links', ['paginator' => $staff])</div>
        @endif
    </div>
</div>
<script>
    function search_staff() {
        var search_staff=document.getElementById('search_staff').value;
        var school_id=document  .getElementById('school_id').value;
        window.location = 'attendance?school_id=' + school_id+'&search_staff='+search_staff;
    }
</script>