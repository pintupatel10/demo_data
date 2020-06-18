@if(Auth::user()->role=="admin")
    <div class="form-group{{ $errors->has('school_id') ? ' has-error' : '' }}">
        <label class="col-sm-4 control-label" for="school_id">School Id <span class="text-red">*</span></label>
        <div class="col-sm-8">
            {!! Form::select('school_id',[''=>'Please Select']+$school_name,null,['id'=>'school_id','class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%','onchange'=> 'getClasses(this.value);']) !!}

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
    <label class="col-sm-4 control-label" for="student_id">Class Name <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::select('class_name',isset($modes_class_name)? $modes_class_name: [],null,['id' => 'class_id','class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%','onchange'=> 'get_division(this.value);']) !!}
        @if ($errors->has('class_name'))
            <span class="help-block">
                <strong>{{ $errors->first('class_name') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="form-group{{ $errors->has('class_division') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="class_division">Class Division <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::select('class_division',isset($modes_class_division)? $modes_class_division: [],null,['id' => 'class_division','class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%']) !!}

        @if ($errors->has('class_division'))
            <span class="help-block">
                <strong>{{ $errors->first('class_division') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="form-group{{ $errors->has('student_id') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="student_id">Student Id <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::select('student_id',isset($modes_student)? $modes_student: [],null,['id' => 'student_id','class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%']) !!}
        @if ($errors->has('student_id'))
            <span class="help-block">
                <strong>{{ $errors->first('student_id') }}</strong>
            </span>
        @endif
    </div>
</div>
{{--<div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">--}}
    {{--<label class="col-sm-4 control-label" for="Uuid">School Name <span class="text-red">*</span></label>--}}
    {{--<div class="col-sm-8">--}}
        {{--{!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'name']) !!}--}}
        {{--@if ($errors->has('name'))--}}
            {{--<span class="help-block">--}}
                {{--<strong>{{ $errors->first('name') }}</strong>--}}
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

<div class="form-group{{ $errors->has('attendance_date') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="attendance_date">Attendance Date <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::text('attendance_date', null, ['class' => 'form-control', 'placeholder' => '','id'=>'datepicker']) !!}
        @if ($errors->has('attendance_date'))
            <span class="help-block">
                <strong>{{ $errors->first('attendance_date') }}</strong>
            </span>
        @endif
    </div>
</div>

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

<div class="form-group{{ $errors->has('status') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="role">Status <span class="text-red">*</span></label>

    <div class="col-sm-8">

        @foreach (\App\School::$status as $key => $value)
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

    function getClasses(val){
        var data = null;
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
       //alert(class_id);
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
        get_student(class_id);
    }

    function get_student(class_id){
        var school_id=document.getElementById('school_id').value;
//        alert('shool'+school_id);
//        alert('class'+class_id);
        $.ajax({
            url: '{{ url('attendance/get_student') }}/'+class_id+'/'+school_id,
            error:function(){
            },
            success: function(result){
               // alert(result);
                $("#student_id").select2().empty();
                $("#student_id").html(result);
                $('#student_id').select2()
            }
        });
    }

    {{--function get_student(val){--}}

        {{--if(val == ''){--}}
            {{--var newval='{{Auth::user()->school_id}}';--}}
            {{--val = newval;--}}
        {{--}--}}

        {{--$.ajax({--}}
            {{--url: '{{ url('attendance/get_student') }}/'+val,--}}
            {{--error:function(){--}}
            {{--},--}}
            {{--success: function(result){--}}
               {{--// alert(result);--}}
                {{--$("#student_id").select2().empty();--}}
                {{--$("#student_id").html(result);--}}
                {{--$('#student_id').select2()--}}

            {{--}--}}
        {{--});--}}
    {{--}--}}

</script>