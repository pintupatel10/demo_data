<div class="form-group{{ $errors->has('roll_no') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="roll_no">Roll No <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::text('roll_no', null, ['class' => 'form-control', 'placeholder' => 'Student role_no']) !!}
        @if ($errors->has('roll_no'))
            <span class="help-block">
                <strong>{{ $errors->first('roll_no') }}</strong>
            </span>
        @endif
    </div>
</div>

@if(Auth::user()->role=="admin")
<div class="form-group{{ $errors->has('school_id') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="school_id">School Id <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::select('school_id',['Please Select']+$name, !empty($modes_selected)?$modes_selected:null,['class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%','onchange'=> 'getClasses(this.value);']) !!}

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
<div class="form-group{{ $errors->has('class_id') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="class_id">Class Id <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::select('class_id',['Please Select']+$name1, !empty($modes)?$modes:null,['class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%', 'id' => 'class_id']) !!}

        @if ($errors->has('class_id'))
            <span class="help-block">
                <strong>{{ $errors->first('class_id') }}</strong>
            </span>
        @endif
    </div>
</div>


<div class="form-group{{ $errors->has('division') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="division">Student Division <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::text('division', null, ['class' => 'form-control', 'placeholder' => 'Student Division']) !!}
        @if ($errors->has('division'))
            <span class="help-block">
                <strong>{{ $errors->first('division') }}</strong>
            </span>
        @endif
    </div>
</div>


<div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="name">Student Name <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Student Name']) !!}
        @if ($errors->has('name'))
            <span class="help-block">
                <strong>{{ $errors->first('name') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="form-group{{ $errors->has('address') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="address">Student Address </label>
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
    <label class="col-sm-4 control-label" for="birthdate">Student BirthDate <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::text('birthdate', null, ['class' => 'form-control', 'placeholder' => '','id'=>'datepicker']) !!}
        @if ($errors->has('birthdate'))
            <span class="help-block">
                <strong>{{ $errors->first('birthdate') }}</strong>
            </span>
        @endif
    </div>
</div>

{{--
<div class="form-group{{ $errors->has('blood_group') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="blood_group">Student Blood Group <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::text('blood_group', null, ['class' => 'form-control', 'placeholder' => 'Student Blood Group']) !!}
        @if ($errors->has('blood_group'))
            <span class="help-block">
                <strong>{{ $errors->first('blood_group') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="form-group{{ $errors->has('mobile') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="mobile">Student Parents Mobile No <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::text('mobile', null, ['class' => 'form-control', 'placeholder' => 'Ex: 9898989898']) !!}
        @if ($errors->has('mobile'))
            <span class="help-block">
                <strong>{{ $errors->first('mobile') }}</strong>
            </span>
        @endif
    </div>
</div>
--}}

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

{{--
<div class="form-group{{ $errors->has('parents_name') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="parents_name">Student Parents Name </label>
    <div class="col-sm-8">
        {!! Form::text('parents_name', null, ['class' => 'form-control', 'placeholder' => 'Student Parents Name']) !!}
        @if ($errors->has('parents_name'))
            <span class="help-block">
                <strong>{{ $errors->first('parents_name') }}</strong>
            </span>
        @endif
    </div>
</div>
--}}

<div class="form-group{{ $errors->has('notes') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="notes">Student Notes </label>
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
    <label class="col-sm-4 control-label" for="rfid_no">Student RFID No </label>
    <div class="col-sm-8">
        {!! Form::text('rfid_no', null, ['class' => 'form-control', 'placeholder' => 'Student RFID No']) !!}
        @if ($errors->has('rfid_no'))
            <span class="help-block">
                <strong>{{ $errors->first('rfid_no') }}</strong>
            </span>
        @endif
    </div>
</div>


<div class="form-group{{ $errors->has('status') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="role">Status <span class="text-red">*</span></label>

    <div class="col-sm-8">

        @foreach (\App\Student::$status as $key => $value)
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
<script type="text/javascript">

    function getClasses(val) {
        if(val == '') val = 0;
        $("#class_id").html('<option value="">Please Select</option>');

        $.ajax({
            url: '{{ url('student/school_class') }}/'+val,
            error:function(){
            },
            success: function(result){
                $("#class_id").html(result).trigger('change');;
            }
        });
    }

</script>