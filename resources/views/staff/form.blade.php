@if(Auth::user()->role=="admin")
<div class="form-group{{ $errors->has('school_id') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="school_id">School Id <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::select('school_id',['Please Select']+$name, !empty($modes_selected)?$modes_selected:null,['class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%']) !!}

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
    <label class="col-sm-4 control-label" for="name">Staff Name <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Staff Name']) !!}
        @if ($errors->has('name'))
            <span class="help-block">
                <strong>{{ $errors->first('name') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="form-group{{ $errors->has('staff_role') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="staff_role">Role<span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::select('staff_role', \App\Staff::$staff_roles, !empty($role_selected)?$role_selected:null,['class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%']) !!}

        @if ($errors->has('staff_role'))
            <span class="help-block">
                <strong>{{ $errors->first('staff_role') }}</strong>
            </span>
        @endif
    </div>
</div>


<div class="form-group{{ $errors->has('address') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="address">Staff Address </label>
    <div class="col-sm-8">
        {!! Form::textarea('address', null, ['class' => 'form-control', 'placeholder' => '']) !!}
        @if ($errors->has('address'))
            <span class="help-block">
                <strong>{{ $errors->first('address') }}</strong>
            </span>
        @endif
    </div>
</div>


<div class="form-group{{ $errors->has('birthdate') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="birthdate">Staff BirthDate <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::text('birthdate', null, ['class' => 'form-control', 'placeholder' => '','id'=>'datepicker']) !!}
        @if ($errors->has('birthdate'))
            <span class="help-block">
                <strong>{{ $errors->first('birthdate') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="form-group{{ $errors->has('blood_group') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="blood_group">Staff Blood Group <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::text('blood_group', null, ['class' => 'form-control', 'placeholder' => 'Staff Blood Group']) !!}
        @if ($errors->has('blood_group'))
            <span class="help-block">
                <strong>{{ $errors->first('blood_group') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="form-group{{ $errors->has('mobile') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="mobile">Staff Mobile No <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::text('mobile', null, ['class' => 'form-control', 'placeholder' => 'Ex: 9898989898']) !!}
        @if ($errors->has('mobile'))
            <span class="help-block">
                <strong>{{ $errors->first('mobile') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="form-group{{ $errors->has('school_time') ? ' has-error' : '' }} ">
    <label class="col-sm-4 control-label" for="school_time">School Time <span class="text-red"></span></label>
    <div class="col-sm-8">
        <div class="input-group bootstrap-timepicker ">
            {!! Form::text('school_time', null, ['class' => 'form-control pull-right timepicker', 'placeholder' => 'School time']) !!}
            <div class="input-group-addon">
                <i class="fa fa-clock-o"></i>
            </div>
        </div>
        @if ($errors->has('school_time'))
            <span class="help-block">
                <strong>{{ $errors->first('school_time') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="form-group{{ $errors->has('notes') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="notes">Staff Notes </label>
    <div class="col-sm-8">
        {!! Form::textarea('notes', null, ['class' => 'form-control', 'placeholder' => '']) !!}
        @if ($errors->has('notes'))
            <span class="help-block">
                <strong>{{ $errors->first('notes') }}</strong>
            </span>
        @endif
    </div>
</div>


<div class="form-group{{ $errors->has('rfid_no') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="rfid_no">Staff RFID No </label>
    <div class="col-sm-8">
        {!! Form::text('rfid_no', null, ['class' => 'form-control', 'placeholder' => 'Staff RFID No']) !!}
        @if ($errors->has('rfid_no'))
            <span class="help-block">
                <strong>{{ $errors->first('rfid_no') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="rfid_no">Staff Userid (Email) <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::email('email', null, ['class' => 'form-control', 'placeholder' => 'Staff Userid']) !!}
        @if ($errors->has('email'))
            <span class="help-block">
                <strong>{{ $errors->first('email') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="password">Password @if (!isset($staff->id)) <span class="text-red">*</span> @endif</label>
    <div class="col-sm-8">
        {!! Form::password('password', ['class' => 'form-control', 'placeholder' => 'Password']) !!}
        @if ($errors->has('password'))
            <span class="help-block">
                <strong>{{ $errors->first('password') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="password_confirmation">Confirm Password @if (!isset($staff->id)) <span class="text-red">*</span> @endif</label>

    <div class="col-sm-8">
        {!! Form::password('password_confirmation', ['class' => 'form-control', 'placeholder' => 'Confirm password']) !!}
        @if ($errors->has('password_confirmation'))
            <span class="help-block">
             <strong>{{ $errors->first('password_confirmation') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="form-group{{ $errors->has('status') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="role">Status <span class="text-red">*</span></label>

    <div class="col-sm-8">

        @foreach (\App\Staff::$status as $key => $value)
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