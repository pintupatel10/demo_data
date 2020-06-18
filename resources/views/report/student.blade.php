
    <div class="form-group{{ $errors->has('report_type') ? ' has-error' : '' }}">
        <label class="col-sm-2 control-label" for="report_type">Report Type <span class="text-red">*</span></label>
        <div class="col-sm-4">
            {!! Form::select('report_type', $reports_student, !empty(\Illuminate\Support\Facades\Input::get('report_type'))?\Illuminate\Support\Facades\Input::get('report_type'):null, ['id'=>'report_type','class' => 'select2 form-control', 'style' => 'width: 100%']) !!}

            @if ($errors->has('report_type'))
                <span class="help-block">
                       <strong>{{ $errors->first('report_type') }}</strong>
                </span>
            @endif
        </div>
    </div>

    @if(Auth::user()->role=="admin")
        <div class="form-group{{ $errors->has('school_id') ? ' has-error' : '' }}">
            <label class="col-sm-2 control-label" for="school_id">School Id <span class="text-red">*</span></label>

            <div class="col-sm-4">
                {!! Form::select('school_id',[''=>'Please Select']+$school_name,!empty(\Illuminate\Support\Facades\Input::get('school_id'))?\Illuminate\Support\Facades\Input::get('school_id'):null,['id'=>'school_id','class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%','onchange'=> 'getClasses(this.value);']) !!}

                @if ($errors->has('school_id'))
                    <span class="help-block">
                         <strong>{{ $errors->first('school_id') }}</strong>
                    </span>
                @endif
            </div>
        </div>
    @else
        {!! Form::hidden('school_id', Auth::user()->school_id,['id'=>'school_id_hidden','onload'=>'getClasses(this.value);']) !!}
    @endif

    <div id="panel-class-device">
    <div class="form-group{{ $errors->has('class_name') ? ' has-error' : '' }}">
        <label class="col-sm-2 control-label" for="class_name">Class Name <span class="text-red">*</span></label>
        <div class="col-sm-4">
            <?php $class=\App\Class_Master::where('id',\Illuminate\Support\Facades\Input::get('class_name'))->lists('name','id');?>
            {!! Form::select('class_name',!empty($class)?$class:[],!empty(\Illuminate\Support\Facades\Input::get('class_name'))? \Illuminate\Support\Facades\Input::get('class_name'):null,['id' => 'class_id','class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%','onchange'=> 'get_division(this.value,0);']) !!}
            @if ($errors->has('class_name'))
                <span class="help-block">
                      <strong>{{ $errors->first('class_name') }}</strong>
                </span>
            @endif
        </div>
    </div>

    <div class="form-group{{ $errors->has('class_division') ? ' has-error' : '' }}">
        <label class="col-sm-2 control-label" for="class_division">Class Division <span class="text-red"></span></label>
        <div class="col-sm-4">
            <?php $division=\App\Division::where('division',\Illuminate\Support\Facades\Input::get('class_division'))->lists('division','division');?>

            {!! Form::select('class_division',!empty($division)?$division:[],!empty(\Illuminate\Support\Facades\Input::get('class_division'))?\Illuminate\Support\Facades\Input::get('class_division'):null,['id' => 'class_division','class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%']) !!}

            @if ($errors->has('class_division'))
                <span class="help-block">
                    <strong>{{ $errors->first('class_division') }}</strong>
                 </span>
            @endif

        </div>
    </div>

    </div>

    <div class="form-group{{ $errors->has('student_id') ? ' has-error' : '' }}">
        <label class="col-sm-2 control-label" for="class_division">Student <span id="show-required-student" class="text-red"> *</span></label>
        <div class="col-sm-4">
            <?php $student=\App\User::where('id',\Illuminate\Support\Facades\Input::get('student_id'))->lists('name','id');?>

            {!! Form::select('student_id',!empty($student)?$student:[],!empty(\Illuminate\Support\Facades\Input::get('student_id'))?\Illuminate\Support\Facades\Input::get('student_id'):null,['id' => 'student_id','class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%']) !!}

            @if ($errors->has('student_id'))
                <span class="help-block">
                    <strong>{{ $errors->first('student_id') }}</strong>
                </span>
            @endif
        </div>
    </div>

    <div id="panel-multiple">

        <div class="form-group {{ $errors->has('from') ? ' has-error' : '' }}">
            <label class="col-sm-2 control-label" for="from">From <span class="text-red">*</span></label>
            <div class="col-sm-4">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                    {!! Form::text('from', !empty(\Illuminate\Support\Facades\Input::get('from'))?\Illuminate\Support\Facades\Input::get('from'):  \Carbon\Carbon::today()->format('Y-m-d'), ['class' => 'form-control', 'id'=>'datepicker']) !!}
                </div>

                @if ($errors->has('from'))
                    <span class="help-block">
                       <strong>{{ $errors->first('from') }}</strong>
                     </span>
                @endif
            </div>
        </div>

        <div class="form-group {{ $errors->has('to') ? ' has-error' : '' }}">
            <label class="col-sm-2 control-label" for="to">To <span class="text-red">*</span></label>
            <div class="col-sm-4">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                    {!! Form::text('to', !empty(\Illuminate\Support\Facades\Input::get('to'))?\Illuminate\Support\Facades\Input::get('to'):  \Carbon\Carbon::today()->format('Y-m-d'), ['class' => 'form-control', 'id'=>'datepicker1']) !!}
                </div>
                @if ($errors->has('to'))
                    <span class="help-block">
                         <strong>{{ $errors->first('to') }}</strong>
                     </span>
                @endif
            </div>
        </div>
    </div>

    <div id="panel-single" class="form-group">
        <div class="{{ $errors->has('date') ? ' has-error' : '' }}">
            <label class="col-sm-2 control-label" for="from">Date <span class="text-red">*</span></label>
            <div class="col-sm-4">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                    {!! Form::text('date', !empty(\Illuminate\Support\Facades\Input::get('date'))?\Illuminate\Support\Facades\Input::get('date'):null, ['class' => 'form-control', 'id'=>'datepicker2']) !!}
                </div>
                @if ($errors->has('date'))
                    <span class="help-block">
                      <strong>{{ $errors->first('date') }}</strong>
                     </span>
                @endif
            </div>
        </div>
    </div>

    <div id="panel-month" class="form-group">
        <div class="{{ $errors->has('month') ? ' has-error' : '' }}">
            <label class="col-sm-2 control-label" for="month">Month <span class="text-red">*</span></label>
            <div class="col-sm-4">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                    {!! Form::text('month',  !empty(\Illuminate\Support\Facades\Input::get('month'))?\Illuminate\Support\Facades\Input::get('month'):null, ['class' => 'form-control', 'id'=>'monthpicker']) !!}
                </div>
                @if ($errors->has('month'))
                    <span class="help-block">
                       <strong>{{ $errors->first('month') }}</strong>
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div id="panel-year" class="form-group">
        <div class="{{ $errors->has('yearfrom') ? ' has-error' : '' }}">
            <label class="col-sm-2 control-label" for="yearfrom">Year From <span class="text-red">*</span></label>
            <div class="col-sm-4">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                    {!! Form::text('yearfrom',  !empty(\Illuminate\Support\Facades\Input::get('yearfrom'))?\Illuminate\Support\Facades\Input::get('yearfrom'):null, ['class' => 'form-control', 'id'=>'yearfrom']) !!}
                </div>
                @if ($errors->has('yearfrom'))
                    <span class="help-block">
                        <strong>{{ $errors->first('yearfrom') }}</strong>
                     </span>
                @endif
            </div>
        </div>
    </div>

    <div id="panel-device">
        <div class="form-group{{ $errors->has('device') ? ' has-error' : '' }}">
            <label class="col-sm-2 control-label" for="device">Device <span class="text-red">*</span></label>
            <div class="col-sm-4">
                <?php $device=\App\Device::where('id',\Illuminate\Support\Facades\Input::get('device'))->lists('device_type','id');?>

                {!! Form::select('device',!empty($device)?$device:[],!empty(\Illuminate\Support\Facades\Input::get('device'))?\Illuminate\Support\Facades\Input::get('device'):null,['id'=>'device','class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%']) !!}

                @if ($errors->has('device'))
                    <span class="help-block">
                      <strong>{{ $errors->first('device') }}</strong>
                    </span>
                @endif
            </div>
        </div>

        {{--<div class="form-group{{ $errors->has('device_month') ? ' has-error' : '' }}">--}}
            {{--<label class="col-sm-2 control-label" for="device_month">Month <span class="text-red"></span></label>--}}
            {{--<div class="col-sm-4">--}}
                {{--<div class="input-group">--}}
                    {{--<span class="input-group-addon"><i class="fa fa-calendar"></i></span>--}}
                    {{--{!! Form::text('device_month', null, ['class' => 'form-control', 'id'=>'monthpicker2']) !!}--}}
                {{--</div>--}}
                {{--@if ($errors->has('device_month'))--}}
                    {{--<span class="help-block">--}}
                          {{--<strong>{{ $errors->first('device_month') }}</strong>--}}
                    {{--</span>--}}
                {{--@endif--}}
            {{--</div>--}}
        {{--</div>--}}
    </div>
