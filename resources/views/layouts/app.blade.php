<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>School | {{ $menu }}</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <meta name="_token" content="{!! csrf_token() !!}"/>
    <!-- Bootstrap 3.3.6 -->
    <link rel="stylesheet" href="{{ URL::asset('assets/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('assets/bootstrap/css/bootstrap.min.css') }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="{{ URL::asset('assets/plugins/daterangepicker/daterangepicker-bs3.css')}}">
    <!-- Date Picker -->
    <link rel="stylesheet" href="{{ URL::asset('assets/plugins/datepicker/datepicker3.css')}}">
    <!-- Icheck radio -->
    <link rel="stylesheet" href="{{ URL::asset('assets/plugins/iCheck/all.css')}}">
    <!-- SELECT  -->
    <link rel="stylesheet" href="{{ URL::asset('assets/plugins/select2/select2.min.css')}}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ URL::asset('assets/dist/css/AdminLTE.min.css') }}">
    <!-- AdminLTE Skins. Choose a skin from the css/skins
         folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="{{ URL::asset('assets/dist/css/skins/_all-skins.min.css') }}">
    <!-- iCheck -->
    <link rel="stylesheet" href="{{ URL::asset('assets/plugins/iCheck/flat/blue.css')}}">
    <!-- Morris chart -->
    <link rel="stylesheet" href="{{ URL::asset('assets/plugins/morris/morris.css')}}">
    <!-- jvectormap -->
    <link rel="stylesheet" href="{{ URL::asset('assets/plugins/jvectormap/jquery-jvectormap-1.2.2.css')}}">
    <!-- Bootstrap time Picker -->
    <link rel="stylesheet" href="{{ URL::asset('assets/plugins/timepicker/bootstrap-timepicker.min.css')}}">

    <style type="text/css">
        .select2-container .select2-selection--single {
            height: 34px !important;
        }
    </style>
    <link rel="stylesheet" href="{{ URL::asset('assets/page_loader.css')}}">

    <!-- bootstrap wysihtml5 - text editor -->
    <link rel="stylesheet" href="{{ URL::asset('assets/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css')}}">
    <!-- Bootstrap datatable -->
    <link rel="stylesheet" href="{{ URL::asset('assets/plugins/datatables/dataTables.bootstrap.css')}}">

    <link rel="stylesheet" href="{{ URL::asset('assets/plugins/timepicker/bootstrap-timepicker.min.css')}}">


    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <!--<style>-->
        <!--.myCheckbox input {-->
        <!--// display: none;-->
        <!--// Better than display: none for accessibility reasons-->
        <!--position: relative;-->
            <!--z-index: -9999;-->
        <!--}-->

        <!--.myCheckbox span {-->
            <!--width: 20px;-->
            <!--height: 20px;-->
            <!--display: block;-->
            <!--background: white;-->
        <!--}-->

        <!--.myCheckbox input:checked + span {-->
            <!--background: green;-->
        <!--}-->
    <!--</style>-->
    <![endif]-->
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

    <header class="main-header">
        <!-- Logo -->
        <a href="{{ url('/dashboard') }}" class="logo">
            <!-- mini logo for sidebar mini 50x50 pixels -->
            <span class="logo-mini"><b>S</b>A</span>
            <!-- logo for regular state and mobile devices -->
            <span class="logo-lg"><b>School</b> Admin</span>
        </a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top">
            <!-- Sidebar toggle button-->
            <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                <span class="sr-only">Toggle navigation</span>
            </a>

            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                    <!-- User Account: style can be found in dropdown.less -->
                    <li class="dropdown user user-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <img src="{{ URL::asset('assets/dist/img/avatar.png') }}" class="user-image"
                                 alt="User Image">
                            <span class="hidden-xs">{{ $user = Auth::user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu">
                            <!-- User image -->
                            <li class="user-header">
                                <img src="{{ URL::asset('assets/dist/img/avatar.png') }}" class="img-circle"
                                     alt="User Image">
                                <p>
                                    {{ $user = Auth::user()->name }} <br>
                                    <small></small>
                                </p>
                            </li>
                            <!-- Menu Footer-->
                            <li class="user-footer">
                                <div class="pull-left">
                                    <a href="{{ url('/users/'.$user = Auth::user()->id).'/edit' }}"
                                       class="btn btn-default btn-flat">Profile</a>
                                </div>
                                <div class="pull-right">
                                    <a href="{{ url('/logout') }}" class="btn btn-default btn-flat">Sign out</a>
                                </div>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
    <!-- Left side column. contains the logo and sidebar -->
    <aside class="main-sidebar">
        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar">
            <!-- Sidebar user panel -->
            <!-- /.search form -->
            <!-- sidebar menu: : style can be found in sidebar.less -->
            <ul class="sidebar-menu">
                <li class="@if($menu=='Dashboard') active  @endif treeview">
                    <a href="{{ url('/dashboard') }}">
                        <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                    </a>
                </li>

                {{--<li class="treeview @if($menu=='User' || $menu=='Staff' ) active  @endif">--}}
                    {{--<a href="#">--}}
                        {{--<i class="fa fa-group"></i> <span>School</span>--}}
                        {{--<i class="fa fa-angle-left pull-right"></i>--}}
                    {{--</a>--}}
                    {{--<ul class="treeview-menu">--}}
                        {{--@if(Auth::user()->role=="admin")--}}
                            {{--<li class="@if(isset($role) && $role=='regional') active @endif"><a--}}
                                        {{--href="{{ url('/regional/users') }}"><i class="fa fa-user"></i> Regional Admin--}}
                                {{--</a></li>--}}
                        {{--@endif--}}
                        {{--@if(Auth::user()->role=="admin" || Auth::user()->role=="regional")--}}
                            {{--<li class="@if(isset($role) && $role=='local') active @endif"><a--}}
                                        {{--href="{{ url('/local/users') }}"><i class="fa fa-user"></i> Local Admin</a></li>--}}
                        {{--@endif--}}
                        {{--@if(Auth::user()->role=="admin" || Auth::user()->role=="local")--}}
                        {{--<li class="@if(isset($menu) && $menu=='Staff') active @endif"><a href="{{ url('/staff') }}"><i--}}
                                        {{--class="fa fa-user"></i> Staff</a></li>--}}
                        {{--@endif--}}

                    {{--</ul>--}}
                {{--</li>--}}

                @if(Auth::user()->role=="admin")
                <li class="treeview @if($menu=='School') active @endif">
                    <a href="{{ url('/school') }}">
                        <i class="fa fa-th"></i> <span>School</span>
                    </a>
                </li>
                @endif

                @if(Auth::user()->role=="admin" || Auth::user()->role=="staff")
                <li class="treeview @if($menu=='Class') active @endif">
                    <a href="{{ url('/class') }}">
                        <i class="fa fa-th"></i> <span>Class</span>
                    </a>
                </li>

                <li class="treeview @if($menu=='Division') active @endif">
                    <a href="{{ url('/division') }}">
                        <i class="fa fa-th"></i> <span>Division</span>
                    </a>
                </li>

                <li class="treeview @if($menu=='Student') active @endif">
                    <a href="{{ url('/student') }}">
                        <i class="fa fa-th"></i> <span>Student</span>
                    </a>
                </li>

                <li class="treeview @if($menu=='Parents') active @endif">
                    <a href="{{ url('/parents') }}">
                        <i class="fa fa-th"></i> <span>Parents</span>
                    </a>
                </li>
              @if(Auth::user()->role=="admin")
              <li class="treeview @if($menu=='Staff') active @endif">
                  <a href="{{ url('/staff') }}">
                      <i class="fa fa-th"></i> <span>Staff</span>
                  </a>
              </li>
             @endif
             @if (Auth::user()->staff_role == 'principal' || Auth::user()->role == 'admin')
              <li class="treeview @if($menu=='Device') active @endif">
                  <a href="{{ url('/device') }}">
                      <i class="fa fa-th"></i> <span>Device</span>
                  </a>
              </li>

              <li class="treeview @if($menu=='Calendar') active @endif">
                  <a href="{{ url('/calendar') }}">
                      <i class="fa fa-th"></i> <span>Calendar</span>
                  </a>
              </li>

                  <li class="treeview @if($menu=='Notification') active @endif">
                  <a href="{{ url('/notification') }}">
                  <i class="fa fa-th"></i> <span>Notification</span>
                  </a>
                  </li>

                  <li class="treeview @if($menu=='Report') active @endif">
                      <a href="{{ url('/report') }}">
                          <i class="fa fa-th"></i> <span>Report</span>
                      </a>
                  </li>
               @endif
                    <li class="treeview @if($menu=='Attendance') active @endif">
                        <a href="{{ url('/attendance') }}">
                            <i class="fa fa-th"></i> <span>Attendance</span>
                        </a>
                    </li>
              @endif

          </ul>

      </section>
      <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  @yield('content')
          <!-- /.content-wrapper -->
  <footer class="main-footer">
      {{--<div class="pull-right hidden-xs">--}}
      {{--<b>Version</b> 2.3.3--}}
      {{--</div>--}}
      <strong>&copy; Alayada Infotech Pvt. Ltd</strong>
  </footer>
  <!-- /.control-sidebar -->
  <!-- Add the sidebar's background. This div must be placed
  immediately after the control sidebar -->
  <div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->

<!-- jQuery 2.2.0 -->
<script src="{{ URL::asset('assets/plugins/jQuery/jQuery-2.2.0.min.js')}}"></script>
<!-- jQuery UI 1.11.4 -->
<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->

<script type="text/javascript">
  $('.calltype').click(function () {
      alert(this.val());
  });
</script>

<script>
  $.widget.bridge('uibutton', $.ui.button);
</script>
<!-- Bootstrap datatables -->
<script src="{{ URL::asset('assets/plugins/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{ URL::asset('assets/plugins/datatables/dataTables.bootstrap.min.js')}}"></script>

<!-- Select2 -->
<script src="{{ URL::asset('assets/plugins/select2/select2.full.min.js')}}"></script>
{{--<script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}" type="text/javascript"></script>--}}
{{--<script src="{{ asset('assets/plugins/datatables/dataTables.bootstrap.min.js') }}" type="text/javascript"></script>--}}
{{--<script src="{{ asset('assets/plugins/datatables/extensions/TableTools/dataTables.tableTools.js') }}" type="text/javascript"></script>--}}


<script>
  $(function () {

      $(".select2").select2();
//Flat red color scheme for iCheck
      $('input[type="checkbox"].flat-red, input[type="radio"].flat-red').iCheck({
          checkboxClass: 'icheckbox_flat-green',
          radioClass: 'iradio_flat-green'
      });
      $("#example1").DataTable();
      $("#example3").DataTable({"paging": false});
      $('#example2').DataTable({
          "paging": true,
          "lengthChange": true,
          "searching": true,
          "ordering": true,
          "info": true,
          "autoWidth": true,
      });

//        $('#example100').DataTable({
//            "paging": true,
//            "lengthChange": true,
//            "searching": true,
//            "ordering": true,
//            "info": true,
//            "autoWidth": true,
//            "dom": 'T<"clear">lfrtip',
//            "tableTools": {
//                "sSwfPath": "/plugins/datatables/extensions/TableTools/swf/copy_csv_xls.swf"}
//        });

      $('#reservation').daterangepicker({
          format: 'YYYY/MM/DD'
      });

      $('#datepicker').datepicker({
          format: 'yyyy-mm-dd',
          autoclose: true
      });
      $('#datepicker1').datepicker({
          format: 'yyyy-mm-dd',
          autoclose: true
      });
      $('#datepicker2').datepicker({
          format: 'yyyy-mm-dd',
          autoclose: true
      });
      $('#datepicker3').datepicker({
          format: 'yyyy-mm-dd',
          autoclose: true
      });

      $('#datepicker4').datepicker({
          format: 'yyyy-mm-dd',
          autoclose: true
      });
      $('#datepicker5').datepicker({
          format: 'yyyy-mm-dd',
          autoclose: true
      });
//Timepicker
      $(".timepicker").timepicker({
          showInputs: false,
          showMeridian: false,
      });


      $('#monthpicker').datepicker({
          format: "yyyy-mm",
          viewMode: "months",
          minViewMode: "months",
          autoclose: true
      });

      $('#monthpicker1').datepicker({
          format: "yyyy-mm",
          viewMode: "months",
          minViewMode: "months",
          autoclose: true
      });
      $('#monthpicker2').datepicker({
          format: "yyyy-mm",
          viewMode: "months",
          minViewMode: "months",
          autoclose: true
      });

      $('#yearfrom').datepicker({
          format: "yyyy",
          autoclose: true,
          minViewMode: "years"
      })    .on('changeDate', function(selected){
          startDate =  $("#from").val();
          $('#to').datepicker('setStartDate', startDate);
      });

      $('#yearfrom1').datepicker({
          format: "yyyy",
          autoclose: true,
          minViewMode: "years"
      })    .on('changeDate', function(selected){
          startDate =  $("#from").val();
          $('#to').datepicker('setStartDate', startDate);
      });
  });
</script>
<script>
  $(document).ready(function() {
      $("#spinningSquaresG").hide();
      $("[id=loader]").css("visibility","visible");
//        $("[id=example1]").css("visibility","visible");
//        $("[id=example2]").css("visibility","visible");
  });

</script>
@yield('jquery')
      <!-- Bootstrap 3.3.6 -->
<script src="{{ URL::asset('assets/bootstrap/js/bootstrap.min.js')}}"></script>


<script src="{{ URL::asset('assets/plugins/iCheck/icheck.min.js')}}"></script>
<!-- Morris.js charts -->

<!-- InputMask -->
<script src="{{ URL::asset('assets/plugins/input-mask/jquery.inputmask.js')}}"></script>
<script src="{{ URL::asset('assets/plugins/input-mask/jquery.inputmask.date.extensions.js')}}"></script>
<script src="{{ URL::asset('assets/plugins/input-mask/jquery.inputmask.extensions.js')}}"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
{{--<script src="{{ URL::asset('assets/plugins/morris/morris.min.js')}}"></script>--}}
<!-- Sparkline -->
<script src="{{ URL::asset('assets/plugins/sparkline/jquery.sparkline.min.js')}}"></script>
<!-- jvectormap -->
<script src="{{ URL::asset('assets/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js')}}"></script>
<script src="{{ URL::asset('assets/plugins/jvectormap/jquery-jvectormap-world-mill-en.js')}}"></script>
<!-- jQuery Knob Chart -->
<script src="{{ URL::asset('assets/plugins/knob/jquery.knob.js')}}"></script>
<!-- daterangepicker -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.11.2/moment.min.js"></script>
<script src="{{ URL::asset('assets/plugins/daterangepicker/daterangepicker.js')}}"></script>
<!-- datepicker -->
<script src="{{ URL::asset('assets/plugins/datepicker/bootstrap-datepicker.js')}}"></script>
<!-- bootstrap time picker -->
<script src="{{ URL::asset('assets/plugins/timepicker/bootstrap-timepicker.min.js')}}"></script>
<!-- Bootstrap WYSIHTML5 -->
<script src="{{ URL::asset('assets/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js')}}"></script>
<!-- Slimscroll -->
<script src="{{ URL::asset('assets/plugins/slimScroll/jquery.slimscroll.min.js')}}"></script>
<!-- FastClick -->
<script src="{{ URL::asset('assets/plugins/fastclick/fastclick.js')}}"></script>
<!-- AdminLTE App -->
<script src="{{ URL::asset('assets/dist/js/app.min.js')}}"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
{{--<script src="{{ URL::asset('assets/dist/js/pages/dashboard.js')}}"></script>--}}

<script src="{{ URL::asset('assets/dist/js/demo.js')}}"></script>

</body>
</html>
