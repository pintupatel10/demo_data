<style type="text/css">
    .suggestionsBox {
        position: absolute;
        //margin: 10px 0px 0 0%;
        width: 250px;
        background-color: #ffffff;
        -moz-border-radius: 7px;
        -webkit-border-radius: 7px;
        color: #1D2C3C;
        z-index:10000;
        height:auto;
        font-size:14px;
        overflow-x: hidden;
    }

    .suggestionList {
        margin: 0px;
        padding: 0px;
        z-index:10000;
    }

    .suggestionList li {

        margin: 0px 0px 3px 0px;
        padding:3px 0 3px 5px;
        cursor: pointer;
        //z-index:10000000;
        z-index:10000;
        list-style:none;
        text-align:left;
    }

    .suggestionList li:hover {
        background-color:#DADADA;
        z-index:10000;
        color:#333333;
    }
    .suggestionsBox_marg{margin: 55px 0px 0 0% !important; width:237px !important;}

    .select2-search__field{
     display: none;!important;
    }
</style>

{{--@if(Auth::user()->role=="admin")--}}
{{--<div class="form-group{{ $errors->has('school_id') ? ' has-error' : '' }}">--}}
    {{--<label class="col-sm-4 control-label" for="school_id">School Id <span class="text-red">*</span></label>--}}
    {{--<div class="col-sm-8">--}}
        {{--{!! Form::select('school_id',['Please Select']+$name, !empty($modes_selected)?$modes_selected:null,['class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%','onchange'=> 'school_students(this.value);']) !!}--}}

        {{--@if ($errors->has('school_id'))--}}
            {{--<span class="help-block">--}}
                {{--<strong>{{ $errors->first('school_id') }}</strong>--}}
            {{--</span>--}}
        {{--@endif--}}
    {{--</div>--}}
{{--</div>--}}
{{--@else--}}
    {{--{!! Form::hidden('school_id', Auth::user()->school_id) !!}--}}
{{--@endif--}}
<div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="name">Parent Name <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Parent Name']) !!}
        @if ($errors->has('name'))
            <span class="help-block">
                <strong>{{ $errors->first('name') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="form-group{{ $errors->has('mobile') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="mobile">Parent Mobile No <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::text('mobile', null, ['class' => 'form-control', 'placeholder' => 'Ex: 9898989898']) !!}
        @if ($errors->has('mobile'))
            <span class="help-block">
                <strong>{{ $errors->first('mobile') }}</strong>
            </span>
        @endif
    </div>
</div>

<div id="myDiv">
<div  class="form-group{{ $errors->has('student_id') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="student_id">Student <span class="text-red">*</span></label>
    <div class="col-sm-6">
        {{--{!! Form::select('student_id', !empty($student)?$student:[], !empty($student_selected)?$student_selected:null,--}}
        {{--['multiple'=>'multiple','name'=>'student_id[]','id'=>'student_id','class' => 'select2 select2-hidden-accessible form-control','style' => 'width: 100%']) !!}--}}
        {{--<input type="text" disabled class="form-control"  value="{{!empty($selected_name)?$selected_name:old('student')}}" name="student" onKeyUp="CallMorethan3(this.value)"  id="student_name"   autocomplete="off"  />--}}
        {{--<input type="hidden" name="student_id" id="student_id" value="{{!empty($selected_id)?$selected_id:old('student_id')}}">--}}
        <select multiple="" name="student_id[]" id="student_id" class="select2 select2-hidden-accessible form-control" style="width: 100%" tabindex="-1" aria-hidden="true">
            <option value="" disabled>click button to add student</option>
            @if(!empty(old('student_id')))
                @foreach(old('student_id') as $list)
                    <?php $st=\App\Student::select('name')->where('id',$list)->first();?>
                    <option selected value="{{$list}}">{{$st->name}}</option>
                @endforeach
            @endif
            @if(!empty($student) && empty(old('student_id')))
            @foreach($student as $list)
            <option selected value="{{$list['id']}}">{{$list['name']}}</option>
            @endforeach
            @endif
        </select>
         @if ($errors->has('student_id'))
            <span class="help-block">
                <strong>{{ $errors->first('student_id') }}</strong>
            </span>
        @endif
        <div class="suggestionsBox suggestionsBox_marg" id="suggestions" style="display: none;">
            <div class="suggestionList" id="autoSuggestionsList">&nbsp;</div>
        </div>
    </div>
    <div class="col-sm-2">
        <button type="button" id="btn_add" name="btn_pass" class="btn btn-info pull-right" onclick="add_student()" >Add More</button>
    </div>

</div>
</div>
<div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="rfid_no">Parent Email <span class="text-red">*</span></label>
    <div class="col-sm-8">
        {!! Form::email('email', null, ['class' => 'form-control', 'placeholder' => 'Parent Email']) !!}
        @if ($errors->has('email'))
            <span class="help-block">
                <strong>{{ $errors->first('email') }}</strong>
            </span>
        @endif
    </div>
</div>

<div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
    <label class="col-sm-4 control-label" for="password">Password @if (!isset($parents->id)) <span class="text-red">*</span> @endif</label>
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
    <label class="col-sm-4 control-label" for="password_confirmation">Confirm Password @if (!isset($parents->id)) <span class="text-red">*</span> @endif</label>

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

        @foreach (\App\Parents::$status as $key => $value)
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

@section('jquery')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.9.3/js/bootstrap-select.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.0/js/materialize.min.js"></script>

@endsection
<script>
    function school_students(val){

        {{--$.ajax({--}}
            {{--url: '{{ url('parents/school_students') }}/'+val,--}}
            {{--success: function(result){--}}
                {{--//alert(result);--}}
                {{--$("#student_id").select2().empty();--}}
                {{--$("#student_id").html(result);--}}
                {{--$('#student_id').select2()--}}

            {{--},--}}
            {{--error:function(){--}}
            {{--}--}}
        {{--});--}}
    }

    function CallMorethan3(str)
    {
        /*get selected values to ignore while gettiing student list*/
        var selectedValues = [];
        var i=0;
        $("#student_id :selected").each(function(){
            i=1;
            selectedValues.push($(this).val());
        });

      /*  if(nothing is selected pass dummy id 0) for routing*/
        if(i == 0){
            selectedValues.push(0);
        }

        if(str.length>=3)
        {
            $.ajax({
             url: '{{ url('parents/get_students') }}/'+str+'/'+selectedValues,
            success: function(result){
                //alert(result);
                document.getElementById("suggestions").style.display="block";
                document.getElementById("autoSuggestionsList").innerHTML=result;
            },
            error:function(){
            }
            });
        }
    }

    function fill(id,name){

        $("#student_id").append('<option selected value="'+id+'">'+name+'</option>');
        $(".select2-selection__rendered").append('<li class="select2-selection__choice" title="'+name+'">' +
                '<span class="select2-selection__choice__remove" role="presentation">×</span>'+name+'</li>');
       // $("#student_id").attr('selected','selected');
        $("#student_id").selectpicker("refresh");

       // document.getElementById("btn_add").style.display ="none";
        document.getElementById("suggestions").style.display="none";
         document.getElementById("btn_add").style.display ="block";
        var d = document.getElementById('myDiv');
        var olddiv = document.getElementById('divIdName');
        d.removeChild(olddiv);
    }

    function add_student(){
        var ni = document.getElementById('myDiv');
        var newdiv = document.createElement('div');
        newdiv.setAttribute("id", 'divIdName');
        newdiv.setAttribute("class", "col-sm-12");
        newdiv.innerHTML = "<div class='form-group'>" +
                "<label style='visibility:hidden;' class='col-sm-4 control-label'>Name<span class='text-red'></span></label>" +
                "<div class='col-sm-8'><input type='text' id=\"get_name" + "\"  placeholder='Type Student name' name=\"name" + "\"  class='form-control' onKeyUp='CallMorethan3(this.value)' autocomplete='off'></div>";
        ni.appendChild(newdiv);
       document.getElementById("btn_add").style.display ="none";
    }

</script>