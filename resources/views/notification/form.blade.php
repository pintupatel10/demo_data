
@if(Auth::user()->role=="admin")
    <div class="form-group{{ $errors->has('school_id') ? ' has-error' : '' }}">
        <label class="col-sm-2 control-label" for="school_id">School Id <span class="text-red">*</span></label>
        <div class="col-sm-4">

            {!! Form::select('school_id',[''=>'Please Select']+$school,null,['id' => 'school_id','class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%','onchange'=> 'getClasses(this.value);']) !!}

            @if ($errors->has('school_id'))
                <span class="help-block">
                        <strong>{{ $errors->first('school_id') }}</strong>
                </span>
            @endif
        </div>
    </div>
@else
    {!! Form::hidden('school_id', Auth::user()->school_id,['id' => 'school_id_hidden']) !!}
@endif

<div class="form-group{{ $errors->has('notification_type') ? ' has-error' : '' }}">
    <label class="col-sm-2 control-label" for="notification_type">Notification Type <span class="text-red">*</span></label>
    <div class="col-sm-4">

    <div class="col-sm-4">
        {{ Form::checkbox('notification_type[]','sms',old('notification_type'),['class' => '','onclick'=>'get_sms_type()','id'=>'sms'  ]) }} <span style="margin-right: 10px">SMS</span>
    </div>
        <div class="col-sm-5">
            {{ Form::checkbox('notification_type[]','notification',old('notification_type'),['class' => '','id'=>'notification','onclick' => 'get_transactional_type()'])  }} <span style="margin-right: 10px">Notification</span>
        </div>

    </div>
    @if ($errors->has('notification_type'))
        <span class="help-block">
             <strong>{{ $errors->first('notification_type') }}</strong>
         </span>
    @endif
     </div>
<div @if(old('notification_type')[0] == 'sms') style="display: block" @else style="display: none" @endif class="form-group{{ $errors->has('sms_type') ? ' has-error' : '' }}" id="sms_type">
    <label class="col-sm-2 control-label" for="sms_type">SMS Type <span class="text-red">*</span></label>
    <div class="col-sm-4">

        <div class="col-sm-4">
            {{ Form::radio('sms_type','promotional',null,['onclick' => 'get_transactional_type()' ]) }} <span style="margin-right: 10px">Promotional</span>
        </div>
        <div class="col-sm-5">
            {{ Form::radio('sms_type','transactional',null,['id' => 'transactional','onclick' => 'get_transactional_type()'])  }} <span style="margin-right: 10px">Transactional</span>
        </div>

    </div>
    @if ($errors->has('sms_type'))
        <span class="help-block">
                <strong>{{ $errors->first('sms_type') }}</strong>
        </span>
    @endif
</div>

<div @if(old('sms_type') == 'transactional') style="display: block" @else style="display: none" @endif id="transactional_type">
<div  class="form-group{{ $errors->has('transactional_type') ? ' has-error' : '' }}" >
    <label class="col-sm-2 control-label" for="sms_type">Transactional Type <span class="text-red">*</span></label>
    <div class="col-sm-4">

        @foreach(\App\Notification::$Transactional_Type as $k => $v)
        <div class="col-sm-4">
            {{ Form::radio('transactional_type',$k,null,['class' => 'flat-red' ]) }} <span style="margin-right: 10px">{{$v}}</span>
        </div>
        @endforeach

        <div class="col-sm-8">
        @if ($errors->has('transactional_type'))
            <span class="help-block">
                <strong>{{ $errors->first('transactional_type') }}</strong>
                </span>
        @endif
        </div>
    </div>
    </div>

    <div  class="form-group{{ $errors->has('date') ? ' has-error' : '' }}" >

    <label class="col-sm-2 control-label" for="date"> Date <span class="text-red">*</span></label>
    <div class="col-sm-4">
        {!! Form::text('date', \Carbon\Carbon::today()->format('Y-m-d'), ['class' => 'form-control', 'placeholder' => '','id'=>'datepicker']) !!}
        @if ($errors->has('date'))
            <span class="help-block">
                <strong>{{ $errors->first('date') }}</strong>
            </span>
        @endif
    </div>
</div>

</div>
<?php $user_role= \Illuminate\Support\Facades\Auth::user()->role;
    $user_staff_role= \Illuminate\Support\Facades\Auth::user()->staff_role;

if($user_role == 'admin'){
    $notification_to=\App\Notification::$notification_admin;
}

if($user_staff_role == 'principal'){
    $notification_to=\App\Notification::$notification_principal;
}

?>
<div  class="form-group{{ $errors->has('notification_to') ? ' has-error' : '' }}">
    <label class="col-sm-2 control-label" for="notification_to">Notification To <span class="text-red">*</span></label>
    <div class="col-sm-4">
        {!! Form::select('notification_to',[''=>'Please Select']+ $notification_to,null,['id'=>'notification_to','class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%',]) !!}

        @if ($errors->has('notification_to'))
            <span class="help-block">
                <strong>{{ $errors->first('notification_to') }}</strong>
                </span>
        @endif
    </div>
</div>

<div id="panel-parent">
    <div class="form-group{{ $errors->has('class_name') ? ' has-error' : '' }}">
        <label class="col-sm-2 control-label" for="student_id">Class Name <span class="text-red"></span></label>
        <div class="col-sm-4">
            {!! Form::select('class_name',[],null,['id' => 'class_id','class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%','onchange'=> 'get_division(this.value);']) !!}
            @if ($errors->has('class_name'))
                <span class="help-block">
                           <strong>{{ $errors->first('class_name') }}</strong>
                </span>
            @endif
        </div>
    </div>

    <div class="form-group{{ $errors->has('class_division') ? ' has-error' : '' }}">
        <label class="col-sm-2 control-label" for="class_division">Class Division <span class="text-red"></span></label>
        <div class="col-sm-4">
            {!! Form::select('class_division',[],null,['id' => 'class_division','class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%']) !!}

            @if ($errors->has('class_division'))
                <span class="help-block">
                                     <strong>{{ $errors->first('class_division') }}</strong>
                </span>
            @endif
        </div>
    </div>

    <div class="form-group{{ $errors->has('student_id') ? ' has-error' : '' }}">
        <label class="col-sm-2 control-label" for="class_division">Student <span class="text-red"></span></label>
        <div class="col-sm-4">
            {!! Form::select('student_id',[],null,['id' => 'student_id','class' => 'select2 select2-hidden-accessible form-control', 'style' => 'width: 100%']) !!}

            @if ($errors->has('student_id'))
                <span class="help-block">
                          <strong>{{ $errors->first('student_id') }}</strong>
                 </span>
            @endif
        </div>
    </div>

</div>

<div class="form-group{{ $errors->has('message') ? ' has-error' : '' }}" id="send_msg" @if(old('notification_type')[0] == 'notification' || old('sms_type') == 'promotional' || (isset(old('notification_type')[1]) && old('notification_type')[1] == 'notification')) style="display: block" @else style="display: none" @endif>

    <label class="col-sm-2 control-label" for="message">Send notification message<span class="text-red">*</span></label>
    <div class="col-sm-4">
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

        window.onload = function(){

            var role ='{{Auth::user()->role}}';

            if(role == 'admin') {
                var school_id = document.getElementById('school_id').value;
            }
            else{
                var school_id = document.getElementById('school_id_hidden').value;
            }

          getClasses(school_id);
        }
        $(function (){
            var reload_inputs = function (){
                var notification_to = $("select[name=notification_to]").val();
                var panel_type = "none";

                 if (notification_to == "{{ \App\Notification::NOTIFICATION_PARENT }}")
                    panel_type = "parent";
                 else
                    panel_type = "none";

                $("#panel-parent").hide();

                if (panel_type == "parent") {
                    $("#panel-parent").show();
                }

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
        }

        function get_division(class_id){
           if(class_id != '') {
               var role = '{{Auth::user()->role}}';

               if (role == 'admin') {
                   var school_id = document.getElementById('school_id').value;
               }
               else {
                   var school_id = document.getElementById('school_id_hidden').value;
               }
               $.ajax({
                   url: '{{ url('attendance/get_division') }}/' + class_id + '/' + school_id,
                   error: function () {
                   },
                   success: function (result) {
                       // alert(result);
                       $("#class_division").select2().empty();
                       $("#class_division").html(result);
                       $('#class_division').select2();
                   }
               });
               get_student(class_id);
           }
        }

        function get_student(class_id){
            var role ='{{Auth::user()->role}}';

            if(role == 'admin') {
                var school_id = document.getElementById('school_id').value;
            }
            else{
                var school_id = document.getElementById('school_id_hidden').value;
            }
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
                document.getElementById('transactional_type').style.display='none';
            }
        }

        function get_transactional_type(){
            var checked=document.getElementById('transactional').checked;
            if(checked == true){
                document.getElementById('transactional_type').style.display='block';
                var noti_check=document.getElementById('notification').checked;

                if(noti_check == true) {
                    document.getElementById('send_msg').style.display = 'block';
                }
                else{
                    document.getElementById('send_msg').style.display = 'none';
                }
            }
            else{
                document.getElementById('transactional_type').style.display='none';
                document.getElementById('send_msg').style.display='block';

            }
        }
    </script>

@endsection
