
@if(Auth::user()->role=="admin")
    <div class="form-group{{ $errors->has('school_id') ? ' has-error' : '' }}">
        <label class="col-sm-3 control-label" for="school_id">School Id <span class="text-red">*</span></label>
        <div class="col-sm-9">

            {!! Form::select('school_id',[''=>'Please Select']+$school,null,['id' => 'school_id','class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%','onchange'=> 'getClasses(this.value);']) !!}

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

<div class="form-group{{ $errors->has('notification_type') ? ' has-error' : '' }}">
    <label class="col-sm-3 control-label" for="notification_type">Notification Type <span class="text-red">*</span></label>
    <div class="col-sm-9">

    <div class="col-sm-4">
        {{ Form::checkbox('notification_type[]','sms',old('notification_type'),['class' => '','onclick'=>'get_sms_type()','id'=>'sms'  ]) }} <span style="margin-right: 10px">SMS</span>
    </div>
        <div class="col-sm-5">
            {{ Form::checkbox('notification_type[]','notification',old('notification_type'),['class' => ''])  }} <span style="margin-right: 10px">Notification</span>
        </div>

    </div>
    @if ($errors->has('notification_type'))
        <span class="help-block">
                <strong>{{ $errors->first('notification_type') }}</strong>
                </span>
    @endif
     </div>
<div @if(old('notification_type')[0] == 'sms') style="display: block" @else style="display: none" @endif class="form-group{{ $errors->has('sms_type') ? ' has-error' : '' }}" id="sms_type">
    <label class="col-sm-3 control-label" for="sms_type">SMS Type <span class="text-red">*</span></label>
    <div class="col-sm-9">

        <div class="col-sm-4">
            {{ Form::radio('sms_type','promotional',null,['class' => 'flat-red' ]) }} <span style="margin-right: 10px">Promotional</span>
        </div>
        <div class="col-sm-5">
            {{ Form::radio('sms_type','transactional',null,['class' => 'flat-red'])  }} <span style="margin-right: 10px">Transactional</span>
        </div>

    </div>
    @if ($errors->has('sms_type'))
        <span class="help-block">
                <strong>{{ $errors->first('sms_type') }}</strong>
                </span>
    @endif
</div>

<div  class="form-group{{ $errors->has('notification_to') ? ' has-error' : '' }}">
    <label class="col-sm-3 control-label" for="school_id">Notification To <span class="text-red">*</span></label>
    <div class="col-sm-9">
        {!! Form::select('notification_to',[''=>'Please Select']+\App\Notification::$notification_to,null,['id'=>'notification_to','class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%',]) !!}

        @if ($errors->has('notification_to'))
            <span class="help-block">
                <strong>{{ $errors->first('notification_to') }}</strong>
                </span>
        @endif
    </div>
</div>



<div id="panel-parent">
    <div class="form-group{{ $errors->has('class_name') ? ' has-error' : '' }}">
        <label class="col-sm-3 control-label" for="student_id">Class Name <span class="text-red"></span></label>
        <div class="col-sm-9">
            {!! Form::select('class_name',[],null,['id' => 'class_id','class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%','onchange'=> 'get_division(this.value);']) !!}
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
            {!! Form::select('class_division',[],null,['id' => 'class_division','class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%']) !!}

            @if ($errors->has('class_division'))
                <span class="help-block">
                                     <strong>{{ $errors->first('class_division') }}</strong>
                </span>
            @endif
        </div>
    </div>

    <div class="form-group{{ $errors->has('student_id') ? ' has-error' : '' }}">
        <label class="col-sm-3 control-label" for="class_division">Student <span class="text-red"></span></label>
        <div class="col-sm-9">
            {!! Form::select('student_id',[],null,['id' => 'student_id','class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%']) !!}

            @if ($errors->has('student_id'))
                <span class="help-block">
                                     <strong>{{ $errors->first('student_id') }}</strong>
                                 </span>
            @endif
        </div>
    </div>

</div>


<div class="form-group{{ $errors->has('message') ? ' has-error' : '' }}">

    <label class="col-sm-3 control-label" for="message">Send notification message<span class="text-red">*</span></label>
    <div class="col-sm-9">
        {!! Form::textarea('message', null, ['class' => 'form-control', 'placeholder' => '','rows'=>'3']) !!}
        @if ($errors->has('message'))
            <span class="help-block">
                    <strong>{{ $errors->first('message') }}</strong>
                </span>
        @endif
    </div>
</div>



@section("jquery")
    <script type="text/javascript">


        $(function (){
            var reload_inputs = function (){
                var notification_to = $("select[name=notification_to]").val();
                var panel_type = "none";

                 if (notification_to == "{{ \App\Notification::NOTIFICATION_PARENT }}")
                    panel_type = "parent";
                 else if (notification_to == "{{ \App\Notification::NOTIFICATION_STUDENT }}")
                    panel_type = "student";

                 {{--else if (notification_to == "{{ \App\Notification::NOTIFICATION_PRINCIPAL }}")--}}
                     {{--panel_type = "principal";--}}
                 {{--else if (notification_to == "{{ \App\Notification::NOTIFICATION_TEACHER}}")--}}
                     {{--panel_type = "teacher";--}}
                 else
                    panel_type = "none";

                $("#panel-parent").hide();
               // $("#panel-school").hide();

                if (panel_type == "parent") {
                    $("#panel-parent").show();
                   // $("#panel-school").show();

                }
                if (panel_type == "student") {
                    $("#panel-parent").show();
                   // $("#panel-school").show();

                }
//                if (panel_type == "principal") {
//                    $("#panel-school").show();
//                }
//                if (panel_type == "teacher") {
//                    $("#panel-school").show();
//                }

            };
            reload_inputs();
            $("#notification_to").change(function (){
                reload_inputs();
            });
        });

        function getClasses(val){

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
            $.ajax({
                url: '{{ url('attendance/get_student') }}/'+class_id +'/'+school_id,
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

        function get_sms_type(){
            var checked=document.getElementById('sms').checked;
            if(checked == true){
                document.getElementById('sms_type').style.display='block';
            }
            else{
                document.getElementById('sms_type').style.display='none';
            }
        }
    </script>

@endsection
