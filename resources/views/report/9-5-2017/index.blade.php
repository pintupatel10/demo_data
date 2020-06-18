@extends('layouts.app')

@section('content')
    <style>
        .select2-container .select2-selection--single {
            height: 34px !important;
        }

    </style>

    <div class="content-wrapper" style="min-height: 946px;">
        <section class="content-header">
                <h1>
                    {{ $menu }}
                </h1>

            <ol class="breadcrumb">
                <li><a href="{{ url('report') }}"> <i class="fa fa-dashboard"></i> </a></li>
                <li class="active">Report</li>
            </ol>
        </section>

        <section class="content">

            <div class="row">
                <div class="col-md-12">
                    @if(Session::has('message'))
                    <div class="alert alert-success alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            {{ Session::get('message') }}
                    </div>
                    @endif

                    <div class="box box-info">
                        <div class="box-header with-border">
                            <h3 class="box-title">Export</h3>
                        </div>
                        {!! Form::open(['url' => url('report/get_report'),'method'=>'post', 'class' => 'form-horizontal','id'=>'form1']) !!}
                        <div class="box-body">

                            <div class="form-group{{ $errors->has('report_for') ? ' has-error' : '' }}">
                                <label class="col-sm-2 control-label" for="role">Report For <span class="text-red">*</span></label>

                                <div class="col-sm-4">

                                    @foreach (\App\Http\Controllers\ReportController::$report_for as $key => $value)
                                        <label>
                                            {!! Form::radio('report_for',$value,old('report_for'), ['id'=>'report_for','class' => '','onclick'=>'get_report_by(this.value);']) !!} <span style="margin-right: 10px">{{ $value }}</span>
                                        </label>
                                    @endforeach

                                    @if ($errors->has('report_for'))
                                        <span class="help-block">
                                 <strong>{{ $errors->first('report_for') }}</strong>
                                </span>
                                  @endif
                                </div>
                            </div>
                            <div id="student_div" style="display: none;">
                                @include('report.student')
                            </div>

                            <div id="staff_div" style="display:none;">
                                @include('report.staff')
                            </div>
                          </div>
                        <div class="box-footer">
                            <button class="btn btn-info pull-left" type="submit"></i> Submit</button>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
            <?php $data=\Illuminate\Support\Facades\Session::get('data');
            ?>
            @if(count($data['data']))
                @include('report.detail')
            @endif
        </section>
    </div>
@endsection


@section("jquery")
    <script type="text/javascript">
        window.onload = function(){
            var report_for = '{{\Illuminate\Support\Facades\Input::old('report_for')}}';
         if(report_for != '') {
           get_report_by(report_for);
          }
            var role ='{{Auth::user()->role}}';
           if(role == 'staff'){
               var school= document.getElementById('staff_school_id_hidden').value;
                get_Device(school);
            }
        };
        function get_report_by(val){
           // alert(val);
          if(val == 'Staff'){
               document.getElementById('staff_div').style.display='block';
               document.getElementById('student_div').style.display='none';

           }
           if(val == 'Student'){
                document.getElementById('staff_div').style.display='none';
                document.getElementById('student_div').style.display='block';
            }
            var role='{{Auth::user()->role}}';
           if(role != 'admin'){
               var school_id= document.getElementById('school_id_hidden').value;
                //alert(student_id);
                getClasses(school_id);
            }
        }
        $(function (){
            //alert('in');

            var reload_inputs = function (){
                var report_type = $("select[name=report_type]").val();
                var panel_type = "single";
               if (report_type == "{{ \App\Http\Controllers\ReportController::REPORT_TODAY }}")
                    panel_type = "none";

                else if (report_type == "{{ \App\Http\Controllers\ReportController::REPORT_DATE }}")
                    panel_type = "single";
                else if (report_type == "{{ \App\Http\Controllers\ReportController::REPORT_PARENT }}")
                    panel_type = "multiple";
                else if (report_type == "{{ \App\Http\Controllers\ReportController::REPORT_MONTH }}")
                    panel_type = "month";
                else if (report_type == "{{ \App\Http\Controllers\ReportController::REPORT_YEAR}}")
                    panel_type = "year";
                else if (report_type == "{{ \App\Http\Controllers\ReportController::REPORT_DEVICE}}")
                    panel_type = "multiple-device";
                else
                     panel_type = "none";

                $("#panel-single").hide();
                $("#panel-multiple").hide();
                $("#panel-month").hide();
                $("#panel-year").hide();
                $("#panel-device").hide();

               if (panel_type == "single") {
                    $("#panel-single").show();
                    $("#panel-class-device").show();
                }

               if (panel_type == "multiple")
                    $("#panel-multiple").show();
                    $("#panel-class-device").show();

               if (panel_type == "month")
                    $("#panel-month").show();
                    $("#panel-class-device").show();

               if (panel_type == "year")
                    $("#panel-year").show();
                    $("#panel-class-device").show();

               if (panel_type == "device") {
                    $("#panel-device").show();
                    $("#panel-class-device").hide();
                }
               if (panel_type == "multiple-device") {
                    $("#panel-device").show();
                    $("#panel-multiple").show();
                   // $("#panel-class-device").show();
                }
            };

            reload_inputs();

            $("#report_type").change(function (){
                reload_inputs();
            });
        });

        var reload_inputs1 = function (){
            //alert('yes');
            var report_type1 = $("select[name=report_type_staff]").val();
            var panel_type1 = "none";
           if (report_type1 == "{{ \App\Http\Controllers\ReportController::REPORT_DATE }}")
                panel_type1 = "singlestaff";

            else if (report_type1 == "{{ \App\Http\Controllers\ReportController::REPORT_MONTH }}")
                panel_type1 = "monthstaff";
            else if (report_type1 == "{{ \App\Http\Controllers\ReportController::REPORT_YEAR}}")
                panel_type1 = "yearstaff";
            else if (report_type1 == "{{ \App\Http\Controllers\ReportController::REPORT_DEVICE}}")
                panel_type1 = "device";
            else
                panel_type1 = "none";

            $("#panel-singlestaff").hide();
            $("#panel-monthstaff").hide();
            $("#panel-yearstaff").hide();
            $("#panel-device_staff").hide();



           if (panel_type1 == "singlestaff")
                $("#panel-singlestaff").show();

           if (panel_type1 == "monthstaff")
                $("#panel-monthstaff").show();
           if (panel_type1 == "yearstaff")
                $("#panel-yearstaff").show();
           if (panel_type1 == "device")
                $("#panel-device_staff").show();
        };

        reload_inputs1();

        $("#report_type_staff").change(function (){
            reload_inputs1();
        });


        function getClasses(val){
         //alert(val);
            $.ajax({
                url: '{{ url('attendance/get_classes') }}/'+val,
                async: false,
                error:function(){
                },
                success: function(result){

                    $("#class_id").select2().empty();
                    $("#class_id").html(result);
                    $("#class_id").select2()
                }
            });
            var class_id=document.getElementById('class_id').value;
            get_division(class_id,val);
        }

        function get_division(class_id,school_id){

            if(school_id == undefined){
                 var school_id=document.getElementById('school_id').value;
                 var role='{{Auth::user()->role}}';
                if(role != 'admin'){
                 var school_id= document.getElementById('school_id_hidden').value;
                 }
             }

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
            get_Device(school_id);
        }

        function get_Device(school_id){
            $.ajax({
                url: '{{ url('report/get_device') }}/'+school_id,
                error:function(){
                },
                success: function(result){
                     //alert(result);
                    /*for staff*/
                    $("#device_staff").select2().empty();
                    $("#device_staff").html(result);
                    $('#device_staff').select2()
                    /*for student*/
                    $("#device").select2().empty();
                    $("#device").html(result);
                    $('#device').select2()
                }
            });
           var role= '{{Auth::user()->role}}';
           if(role == 'staff'){
                var staff_school_id= document.getElementById('staff_school_id_hidden').value;
            }
            else {
                var staff_school_id = document.getElementById('staff_school_id').value;
            }
            get_staff_teacher(staff_school_id);
        }

        function get_staff_teacher(school_id){
            $.ajax({
                url: '{{ url('report/get_staff_teacher') }}/'+school_id,
                error:function(){
                },
                success: function(result){
                    // alert(result);
                    $("#staff_id").select2().empty();
                    $("#staff_id").html(result);
                    $('#staff_id').select2()
                }
            });
        }
    </script>

@endsection
