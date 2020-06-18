@if(Auth::user()->role=="admin")
<div class="form-group{{ $errors->has('school_id') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="school_id">School Id <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::select('school_id',['please Select']+$name, !empty($modes_selected)?$modes_selected:null,['class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%']) !!}

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
    <label class="col-sm-4 control-label" for="name">Class Name <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Class Name']) !!}
        @if ($errors->has('name'))
            <span class="help-block">
                <strong>{{ $errors->first('name') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="form-group{{ $errors->has('status') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="role">Status <span class="text-red">*</span></label>

    <div class="col-sm-8">

        @foreach (\App\Class_Master::$status as $key => $value)
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


