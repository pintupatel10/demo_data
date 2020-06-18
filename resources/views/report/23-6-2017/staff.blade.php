
    <div class="form-group{{ $errors->has('report_type') ? ' has-error' : '' }}">
        <label class="col-sm-2 control-label" for="report_type">Report Type <span class="text-red">*</span></label>
        <div class="col-sm-4">
            {!! Form::select('report_type_staff', $reports_staff, !empty(\Illuminate\Support\Facades\Input::get('report_type_staff'))?\Illuminate\Support\Facades\Input::get('report_type_staff'):null, ['id'=>'report_type_staff','class' => 'select2 form-control', 'style' => 'width: 100%']) !!}

            @if ($errors->has('report_type'))
                <span class="help-block">
                    <strong>{{ $errors->first('report_type') }}</strong>
                </span>
            @endif
        </div>
    </div>

    @if(Auth::user()->role=="admin")
        <div class="form-group{{ $errors->has('staff_school_id') ? ' has-error' : '' }}">
            <label class="col-sm-2 control-label" for="staff_school_id">School Id <span class="text-red">*</span></label>
            <div class="col-sm-4">
                {!! Form::select('staff_school_id',[''=>'Please Select']+$school_name,!empty(\Illuminate\Support\Facades\Input::get('staff_school_id'))?\Illuminate\Support\Facades\Input::get('staff_school_id'):null,['id'=>'staff_school_id','class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%','onchange'=> 'get_Device(this.value);']) !!}

                @if ($errors->has('staff_school_id'))
                    <span class="help-block">
                       <strong>{{ $errors->first('staff_school_id') }}</strong>
                    </span>
                @endif
            </div>
        </div>
    @else
        {!! Form::hidden('staff_school_id', Auth::user()->school_id,['id'=>'staff_school_id_hidden']) !!}
    @endif

    @if(Auth::user()->staff_role =='teacher')
        {!! Form::hidden('staff_id', Auth::user()->id) !!}
    @else
        <div class="form-group{{ $errors->has('staff_id') ? ' has-error' : '' }}">
            <label class="col-sm-2 control-label" for="staff_id">Staff <span class="text-red"></span></label>
            <div class="col-sm-4">
                <?php $staff=\App\User::where('id',\Illuminate\Support\Facades\Input::get('staff_id'))->lists('name','id');?>

                {!! Form::select('staff_id',!empty($staff)?$staff:[],!empty(\Illuminate\Support\Facades\Input::get('staff_id'))?\Illuminate\Support\Facades\Input::get('staff_id'):null,['id'=>'staff_id','class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%']) !!}

                @if ($errors->has('staff_id'))
                    <span class="help-block">
                      <strong>{{ $errors->first('staff_id') }}</strong>
                    </span>
                @endif
            </div>
        </div>
    @endif

    <div id="panel-singlestaff" class="form-group">
        <div class="{{ $errors->has('date_staff') ? ' has-error' : '' }}">
            <label class="col-sm-2 control-label" for="date">Date <span class="text-red">*</span></label>
            <div class="col-sm-4">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                    {!! Form::text('date_staff', !empty(\Illuminate\Support\Facades\Input::get('date_staff'))?\Illuminate\Support\Facades\Input::get('date_staff'):null, ['class' => 'form-control', 'id'=>'datepicker3']) !!}
                </div>
                @if ($errors->has('date_staff'))
                    <span class="help-block">
                                                <strong>{{ $errors->first('date_staff') }}</strong>
                                            </span>
                @endif
            </div>
        </div>
    </div>

    <div id="panel-monthstaff" class="form-group">
        <div class="{{ $errors->has('month_staff') ? ' has-error' : '' }}">
            <label class="col-sm-2 control-label" for="month">Month <span class="text-red">*</span></label>
            <div class="col-sm-4">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                    {!! Form::text('month_staff', !empty(\Illuminate\Support\Facades\Input::get('month_staff'))?\Illuminate\Support\Facades\Input::get('month_staff'):null, ['class' => 'form-control', 'id'=>'monthpicker1']) !!}
                </div>
                @if ($errors->has('month_staff'))
                    <span class="help-block">
                    <strong>{{ $errors->first('month_staff') }}</strong>
                </span>
                @endif
            </div>
        </div>
    </div>

    <div id="panel-yearstaff" class="form-group">
        <div class="{{ $errors->has('yearfrom_staff') ? ' has-error' : '' }}">
            <label class="col-sm-2 control-label" for="yearfrom">Year From <span class="text-red">*</span></label>
            <div class="col-sm-4">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                    {!! Form::text('yearfrom_staff', !empty(\Illuminate\Support\Facades\Input::get('yearfrom_staff'))?\Illuminate\Support\Facades\Input::get('yearfrom_staff'):null, ['class' => 'form-control', 'id'=>'yearfrom1']) !!}

                </div>
                @if ($errors->has('yearfrom_staff'))
                    <span class="help-block">
                       <strong>{{ $errors->first('yearfrom_staff') }}</strong>
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div id="panel-device_staff">

        <div class="form-group{{ $errors->has('device_staff') ? ' has-error' : '' }}">
            <label class="col-sm-2 control-label" for="device_staff">Device <span class="text-red">*</span></label>
            <div class="col-sm-4">
                <?php $device=\App\Device::where('id',\Illuminate\Support\Facades\Input::get('device_staff'))->lists('device_type','id');?>

                {!! Form::select('device_staff',!empty($device)?$device:[],!empty(\Illuminate\Support\Facades\Input::get('device_staff'))?\Illuminate\Support\Facades\Input::get('device_staff'):null,['id'=>'device_staff','class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%']) !!}
                @if ($errors->has('device_staff'))
                    <span class="help-block">
                      <strong>{{ $errors->first('device_staff') }}</strong>
                    </span>
                @endif
            </div>
        </div>
        {{--@if(Auth::user()->staff_role =='teacher')--}}
            {{--{!! Form::hidden('staff_id', Auth::user()->id) !!}--}}
        {{--@else--}}
        {{--<div class="form-group{{ $errors->has('staff_id') ? ' has-error' : '' }}">--}}
            {{--<label class="col-sm-2 control-label" for="staff_id">Staff <span class="text-red">*</span></label>--}}
            {{--<div class="col-sm-4">--}}
                {{--<div class="input-group">--}}
                    {{--<span class="input-group-addon"><i class="fa fa-calendar"></i></span>--}}
                    {{--{!! Form::select('staff_id',[],!empty(\Illuminate\Support\Facades\Input::get('staff_id'))?\Illuminate\Support\Facades\Input::get('staff_id'):null,['id'=>'staff_id','class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%']) !!}--}}

                {{--</div>--}}
                {{--@if ($errors->has('staff_id'))--}}
                    {{--<span class="help-block">--}}
                      {{--<strong>{{ $errors->first('staff_id') }}</strong>--}}
                    {{--</span>--}}
                {{--@endif--}}
            {{--</div>--}}
        {{--</div>--}}
        {{--@endif--}}
        <div class="form-group {{ $errors->has('staff_date_from') ? ' has-error' : '' }}">
            <label class="col-sm-2 control-label" for="staff_date_from">From <span class="text-red">*</span></label>
            <div class="col-sm-4">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                    {!! Form::text('staff_date_from',!empty(\Illuminate\Support\Facades\Input::get('staff_date_from'))?\Illuminate\Support\Facades\Input::get('staff_date_from'): \Carbon\Carbon::today()->format('Y-m-d'), ['class' => 'form-control', 'id'=>'datepicker4']) !!}
                </div>

                @if ($errors->has('staff_date_from'))
                    <span class="help-block">
                                                <strong>{{ $errors->first('staff_date_from') }}</strong>
                                            </span>
                @endif
            </div>
        </div>
        <div class="form-group {{ $errors->has('staff_date_to') ? ' has-error' : '' }}">
            <label class="col-sm-2 control-label" for="staff_date_to">To <span class="text-red">*</span></label>
            <div class="col-sm-4">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                    {!! Form::text('staff_date_to',!empty(\Illuminate\Support\Facades\Input::get('staff_date_to'))?\Illuminate\Support\Facades\Input::get('staff_date_to'):  \Carbon\Carbon::today()->format('Y-m-d'), ['class' => 'form-control', 'id'=>'datepicker5']) !!}
                </div>
                @if ($errors->has('staff_date_to'))
                    <span class="help-block">
                    <strong>{{ $errors->first('staff_date_to') }}</strong>
                      </span>
                @endif
            </div>
        </div>
    </div>
