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
    {!! Form::hidden('school_id', Auth::user()->school_id,['id'=>'school_id_hidden']) !!}
@endif

<div class="form-group{{ $errors->has('class_name') ? ' has-error' : '' }}">
    <label class="col-sm-3 control-label" for="student_id">Class Name <span class="text-red"></span></label>
    <div class="col-sm-9">
        {!! Form::select('class_name',[''=>'Please Select']+$all_class,!empty(\Illuminate\Support\Facades\Input::get('class_name'))?\Illuminate\Support\Facades\Input::get('class_name'):null,['id' => 'class_id','class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%','onchange'=> 'get_division(this.value);']) !!}
        @if ($errors->has('class_name'))
            <span class="help-block">
               <strong>{{ $errors->first('class_name') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="form-group{{ $errors->has('class_division') ? ' has-error' : '' }}">
    <label class="col-sm-3 control-label" for="class_division">Class Division <span class="text-red"></span></label>
    <div class="col-sm-9">
        {!! Form::select('class_division',[''=>'Please Select']+$all_division,!empty(\Illuminate\Support\Facades\Input::get('class_division'))?\Illuminate\Support\Facades\Input::get('class_division'):null,['id' => 'class_division','class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%']) !!}

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



{{--<input type="hidden" name="showlog" value="0" id="showlog">--}}

<!-- /.box-body -->