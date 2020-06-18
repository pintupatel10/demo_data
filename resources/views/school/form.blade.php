<div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="Uuid">School Name <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'name']) !!}
        @if ($errors->has('name'))
            <span class="help-block">
                <strong>{{ $errors->first('name') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="form-group{{ $errors->has('medium') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="role">School Medium <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::select('medium', \App\School::$medium, null, ['class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%']) !!}

        @if ($errors->has('medium'))
            <span class="help-block">
                <strong>{{ $errors->first('medium') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="form-group{{ $errors->has('type') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="role">School Type <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::select('type', \App\School::$type, null, ['class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%']) !!}

        @if ($errors->has('type'))
            <span class="help-block">
                <strong>{{ $errors->first('type') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="form-group{{ $errors->has('phone') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="label">School Phone <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::text('phone', null, ['class' => 'form-control', 'placeholder' => 'Ex: 000-2222222']) !!}
        @if ($errors->has('phone'))
            <span class="help-block">
                <strong>{{ $errors->first('phone') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="form-group{{ $errors->has('mobile') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="model">School Mobile <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::text('mobile', null, ['class' => 'form-control', 'placeholder' => 'Ex: 9898989898']) !!}
        @if ($errors->has('mobile'))
            <span class="help-block">
                <strong>{{ $errors->first('mobile') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="form-group{{ $errors->has('website') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="model">School Website <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::text('website', null, ['class' => 'form-control', 'placeholder' => 'website']) !!}
        @if ($errors->has('website'))
            <span class="help-block">
                <strong>{{ $errors->first('website') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="form-group{{ $errors->has('image') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="image">image<span class="text-red">*</span></label>
    <div class="col-sm-8">
        <div class="">

            {!! Form::file('image', ['class' => '', 'id'=> 'image', 'onChange'=>'AjaxUploadImage(this)']) !!}
        </div>

        <?php
        if (!empty($school->image) && $school->image != "") {
        ?>
        <br><img id="DisplayImage" src="{{ url($school->image) }}" name="img" id="img" width="150" style="padding-bottom:5px" >
        <?php
        }else{
            echo '<br><img id="DisplayImage" src="" width="150" style="display: none;"/>';
        } ?>

        @if ($errors->has('image'))
            <span class="help-block">
                    <strong>{{ $errors->first('image') }}</strong>
                </span>
        @endif
    </div>
</div>

<div class="form-group{{ $errors->has('principal_name') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="major">School Principal Name </label>
    <div class="col-sm-8">
        {!! Form::text('principal_name', null, ['class' => 'form-control', 'placeholder' => 'Principal name']) !!}
        @if ($errors->has('principal_name'))
            <span class="help-block">
                <strong>{{ $errors->first('principal_name') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="form-group{{ $errors->has('trustee_name') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="minor">School Trustee Name </label>
    <div class="col-sm-8">
        {!! Form::text('trustee_name', null, ['class' => 'form-control', 'placeholder' => 'Trustee name']) !!}
        @if ($errors->has('trustee_name'))
            <span class="help-block">
                <strong>{{ $errors->first('trustee_name') }}</strong>
            </span>
        @endif
    </div>
</div>


<div class="form-group{{ $errors->has('detail') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="minor">School Detail </label>
    <div class="col-sm-8">
        {!! Form::textarea('detail', null, ['class' => 'form-control', 'placeholder' => '']) !!}
        @if ($errors->has('detail'))
            <span class="help-block">
                <strong>{{ $errors->first('detail') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="form-group{{ $errors->has('tot_strength') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="minor">School Total Strength </label>
    <div class="col-sm-8">
        {!! Form::text('tot_strength', null, ['class' => 'form-control', 'placeholder' => '0']) !!}
        @if ($errors->has('tot_strength'))
            <span class="help-block">
                <strong>{{ $errors->first('tot_strength') }}</strong>
            </span>
        @endif
    </div>
</div>



<div class="form-group{{ $errors->has('start_time') ? ' has-error' : '' }} ">
    <label class="col-sm-4 control-label" for="start_time">School Start Time<span class="text-red"></span></label>
    <div class="col-sm-8">
        <div class="input-group bootstrap-timepicker ">
            {!! Form::text('start_time', null, ['class' => 'form-control pull-right timepicker', 'placeholder' => 'Start Time']) !!}
            <div class="input-group-addon">
                <i class="fa fa-clock-o"></i>
            </div>
        </div>
        @if ($errors->has('start_time'))
            <span class="help-block">
                <strong>{{ $errors->first('start_time') }}</strong>
            </span>
        @endif
    </div>
</div>


<div class="form-group{{ $errors->has('end_time') ? ' has-error' : '' }} ">
    <label class="col-sm-4 control-label" for="start_time">School End Time<span class="text-red"></span></label>
    <div class="col-sm-8">
        <div class="input-group bootstrap-timepicker ">
            {!! Form::text('end_time', null, ['class' => 'form-control pull-right timepicker', 'placeholder' => 'End Time']) !!}
            <div class="input-group-addon">
                <i class="fa fa-clock-o"></i>
            </div>
        </div>
        @if ($errors->has('end_time'))
            <span class="help-block">
                <strong>{{ $errors->first('end_time') }}</strong>
            </span>
        @endif
    </div>
</div>


<div class="form-group{{ $errors->has('week_start_time') ? ' has-error' : '' }} ">
    <label class="col-sm-4 control-label" for="start_time">School Week Start Time<span class="text-red"></span></label>
    <div class="col-sm-8">
        <div class="input-group bootstrap-timepicker ">
            {!! Form::text('week_start_time', null, ['class' => 'form-control pull-right timepicker', 'placeholder' => 'Start Time']) !!}
            <div class="input-group-addon">
                <i class="fa fa-clock-o"></i>
            </div>
        </div>
        @if ($errors->has('week_start_time'))
            <span class="help-block">
                <strong>{{ $errors->first('week_start_time') }}</strong>
            </span>
        @endif
    </div>
</div>


<div class="form-group{{ $errors->has('week_end_time') ? ' has-error' : '' }} ">
    <label class="col-sm-4 control-label" for="week_end_time">School Week End Time<span class="text-red"></span></label>
    <div class="col-sm-8">
        <div class="input-group bootstrap-timepicker ">
            {!! Form::text('week_end_time', null, ['class' => 'form-control pull-right timepicker', 'placeholder' => 'End Time']) !!}
            <div class="input-group-addon">
                <i class="fa fa-clock-o"></i>
            </div>
        </div>
        @if ($errors->has('week_end_time'))
            <span class="help-block">
                <strong>{{ $errors->first('week_end_time') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="form-group{{ $errors->has('refer_by') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="minor">School Refer By</label>
    <div class="col-sm-8">
        {!! Form::text('refer_by', null, ['class' => 'form-control', 'placeholder' => '']) !!}
        @if ($errors->has('refer_by'))
            <span class="help-block">
                <strong>{{ $errors->first('refer_by') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="form-group{{ $errors->has('market_by') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="minor">School Market By</label>
    <div class="col-sm-8">
        {!! Form::text('market_by', null, ['class' => 'form-control', 'placeholder' => '']) !!}
        @if ($errors->has('market_by'))
            <span class="help-block">
                <strong>{{ $errors->first('market_by') }}</strong>
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



<script>

    $("#image").fileinput({
        showUpload: false,
        showCaption: false,
        showPreview: false,
        showRemove: false,
        browseClass: "btn btn-primary btn-lg btn_new",
    });

    function AjaxUploadImage(obj,id){

        var file = obj.files[0];
        var imagefile = file.type;
        var match = ["image/jpeg", "image/png", "image/jpg"];
        if (!((imagefile == match[0]) || (imagefile == match[1]) || (imagefile == match[2])))
        {
            $('#previewing'+URL).attr('src', 'noimage.png');
            alert("<p id='error'>Please Select A valid Image File</p>" + "<h4>Note</h4>" + "<span id='error_message'>Only jpeg, jpg and png Images type allowed</span>");
            //$("#message").html("<p id='error'>Please Select A valid Image File</p>" + "<h4>Note</h4>" + "<span id='error_message'>Only jpeg, jpg and png Images type allowed</span>");
            return false;
        } else{
            var reader = new FileReader();
            reader.onload = imageIsLoaded;
            reader.readAsDataURL(obj.files[0]);
        }
    }

    function imageIsLoaded(e) {

        $('#DisplayImage').css("display", "block");
        $('#DisplayImage').attr('src', e.target.result);
        $('#DisplayImage').attr('width', '150');

    };

</script>


