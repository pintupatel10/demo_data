<div class="form-group{{ $errors->has('school_id') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="school_id">School <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::select('school_id',['please Select']+$name, !empty($modes_selected)?$modes_selected:null,['class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%','onchange'=> 'getClasses(this.value);']) !!}

        @if ($errors->has('school_id'))
            <span class="help-block">
                <strong>{{ $errors->first('school_id') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="form-group{{ $errors->has('class_id') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="class_id">Class <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::select('class_id',isset($name1)? $name1: [], !empty($modes)?$modes:null,['id' => 'class_id', 'class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%']) !!}

        @if ($errors->has('class_id'))
            <span class="help-block">
                <strong>{{ $errors->first('class_id') }}</strong>
            </span>
        @endif
    </div>
</div>


<div class="form-group{{ $errors->has('division') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="division">Division <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::text('division', null, ['class' => 'form-control', 'placeholder' => 'Ex: A']) !!}
        @if ($errors->has('division'))
            <span class="help-block">
                <strong>{{ $errors->first('division') }}</strong>
            </span>
        @endif
    </div>
</div>


<div class="form-group{{ $errors->has('status') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="role">Status <span class="text-red">*</span></label>

    <div class="col-sm-8">

        @foreach (\App\Division::$status as $key => $value)
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

        $.ajax({
            url: '{{ url('class/get_classes') }}/'+val,
            error:function(){
            },
            success: function(result){
                //alert(result);
                $("#class_id").select2().empty();
                $("#class_id").html(result);
                $('#class_id').select2()

            }
        });
    }

</script>