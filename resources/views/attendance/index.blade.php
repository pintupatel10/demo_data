@extends('layouts.app')
<style>
    .select2-container .select2-selection--single {
        height: 34px !important;
    }

</style>
@section('content')
    <div class="content-wrapper" style="min-height: 946px;">
        <!-- Content Header (Page header) -->

        <section class="content">
            @include ('error')
            <div class="box box-info">
                @if(\Illuminate\Support\Facades\Session::has('message'))
                    <div class="alert alert-success alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        {{ \Illuminate\Support\Facades\Session::get('message') }}
                    </div>
                @endif
            <!-- Get  Attendance Log  Form  -->
            <div class="row">

                <!-- right column -->
                <div class="col-md-6">
                    <!-- Horizontal Form -->

                        <div class="box-header with-border">
                            <h3 class="box-title">ATTENDANCE</h3>
                        </div>
                        <!-- /.box-header -->
                        <!-- form start -->

                        {!! Form::open(['url' => url('attendance'), 'method' => 'get', 'class' => 'form-horizontal','files'=>true,'name'=>'filter_form']) !!}
                        <div class="box-body">
                            @include('attendance.filter_form')
                        </div>
                        <div class="box-footer">
                            <button class="btn btn-info pull-left"  value="submit" name="submit" type="submit">Submit</button>
                            &nbsp;&nbsp;<a href="{{ url('/attendance') }}" class="btn btn-info pull-right">Clear</a>
                        </div>
                        <!-- /.box-footer -->
                        {!! Form::close() !!}
                      </div>
                <div class="col-md-6">
                    <!-- Attendance Log -->
                        <div class="box-header with-border">
                            <h3 class="box-title">ATTENDANCE Log</h3>
                        </div>
                        <div class="box-body">
                            <div id="orders-container">

                                <div class="order-body">
                                    <?php $role=\Illuminate\Support\Facades\Auth::user()->role;?>
                                    <table class="table table-bordered">
                                        <thead>
                                        <tr>
                                            <th></th>
                                            <th>Student</th>
                                           @if($role == 'admin') <th>Staff</th>@endif
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <th>Total : </th><th>{{$total_student}}</th> @if($role == 'admin')<th>{{$total_staff}}</th> @endif
                                        </tr>
                                        <tr>
                                            <th>Present :</th><th> {{$present}}</th> @if($role == 'admin')<th>{{$staff_present}}</th> @endif
                                        </tr>
                                        <tr>
                                            <th>Absent : </th><th>{{$absent}}</th> @if($role == 'admin')<th>{{$staff_absent}}</th> @endif
                                        </tr>
                                        <tr>
                                            <th>On Leave : </th><th>{{$leave}}</th> @if($role == 'admin')<th>{{$staff_leave}}</th> @endif
                                        </tr>
                                        </tbody>
                                       </table>
                                        {{--<table class="table table-bordered">--}}

                                        {{--<tbody>--}}
                                            {{--<tr>--}}
                                                {{--<th>Total Staff : {{$total_staff}}</th>--}}

                                            {{--</tr>--}}
                                            {{--<tr>--}}
                                                {{--<th>Present : {{$staff_present}}</th>--}}

                                            {{--</tr>--}}
                                            {{--<tr>--}}
                                                {{--<th>Absent : {{$staff_absent}}</th>--}}

                                            {{--</tr>--}}
                                            {{--<tr>--}}
                                                {{--<th>On Leave : {{$staff_leave}}</th>--}}
                                            {{--</tr>--}}
                                          {{--</tbody>--}}
                                        {{--</table>--}}

                                </div>
                                <div class="order-footer">

                                </div>
                            </div>
                        </div>
                    <!-- Attendance Log End  -->
                </div>
                </div>

            </div>

            <!-- Get  Attendance Log  Form End -->

            <!-- View List Attendance Log  -->
             @include('attendance.student_attendance')
            <!-- View List Attendance Log End  -->
            <!-- View List Attendance Staff Log  -->
            @if(\Illuminate\Support\Facades\Auth::user()->role == 'admin')
            @include('attendance.staff_attendance')
            @endif
            <!-- View List Attendance Log staff End  -->


        </section>
    </div>

@endsection

<script type="text/javascript">

    window.onload = function(){

        var role ='{{Auth::user()->role}}';

        if(role == 'staff'){
            var school= document.getElementById('school_id_hidden').value;
            //alert(school);
            getClasses(school);
        }
    };

    function getClasses(val){
        if(val != '') {
            $.ajax({
                url: '{{ url('attendance/get_classes') }}/' + val,
                async: false,
                error: function () {
                },
                success: function (result) {

                    $("#class_id").select2().empty();
                    $("#class_id").html(result);
                    $('#class_id').select2();
                }
            });
        }
    }

    function get_division(class_id){

        if(class_id != '') {

            var role ='{{Auth::user()->role}}';

            if(role == 'admin') {
                var school_id = document.getElementById('school_id').value;
            }
            else{
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
                    $('#class_division').select2()
                }
            });
        }
    }
</script>
