@if(Auth::user()->role=="admin")
    <div class="form-group{{ $errors->has('school_id') ? ' has-error' : '' }}">
        <label class="col-sm-4 control-label" for="school_id">School Id <span class="text-red">*</span></label>
        <div class="col-sm-8">
            {!! Form::select('school_id', $name, !empty($modes_selected)?$modes_selected:null,['class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%']) !!}

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

<div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="Uuid">Device Name <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Name']) !!}
        @if ($errors->has('name'))
            <span class="help-block">
                <strong>{{ $errors->first('name') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="form-group{{ $errors->has('serial_no') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="Uuid">Device Serial No <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::text('serial_no', null, ['class' => 'form-control', 'placeholder' => 'Serial No']) !!}
        @if ($errors->has('serial_no'))
            <span class="help-block">
                <strong>{{ $errors->first('serial_no') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="form-group{{ $errors->has('location') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="Uuid">Device Location <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::text('location', null, ['class' => 'form-control', 'placeholder' => 'Location']) !!}
        @if ($errors->has('location'))
            <span class="help-block">
                <strong>{{ $errors->first('location') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="form-group{{ $errors->has('description') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="address">Device Description </label>
    <div class="col-sm-8">
        {!! Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => '']) !!}
        @if ($errors->has('description'))
            <span class="help-block">
                <strong>{{ $errors->first('description') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="form-group{{ $errors->has('device_type') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="role">Device Type <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::select('device_type', \App\Device::$device_type, null, ['class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%']) !!}

        @if ($errors->has('device_type'))
            <span class="help-block">
                <strong>{{ $errors->first('device_type') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="form-group{{ $errors->has('status') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="role">Status <span class="text-red">*</span></label>
    <div class="col-sm-8">

        @foreach (\App\Device::$status as $key => $value)
            <label>
                {!! Form::radio('status', $key, ($key=='active') ? true:false, ['class' => 'flat-red']) !!} <span style="margin-right: 10px">{{ $value }}</span>
            </label>
        @endforeach

        @if ($errors->has('status'))
            <span class="help-block">
             <strong>{{ $errors->first('status') }}</strong>
            </span>
        @endif
    </div>
</div>
