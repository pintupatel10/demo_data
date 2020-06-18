@extends('layouts.app')
@section('content')
    <style>
        .table{
            overflow-y: scroll;
        }
    </style>
    <style>

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
    </style>

    <div class="content-wrapper" style="min-height: 946px;">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                {{ $menu }}
                <small>View</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="#">{{ $menu }}</a></li>
                <li class="active">View</li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="row">
                <div class="col-md-12">

                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title">View Attendance Detail</h3>
                        </div>
                        <div class="box-body" style="overflow-x:auto;">
                            <table  class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>School Name</th>
                                    <th>Name</th>
                                    <th>Class Name</th>
                                    <th>Division</th>
                                    <th>In-Time</th>
                                    <th>Out-Time</th>
                                    <th>Attendance Date</th>
                                    <th>On Leave</th>
                                    <th>Attendance Time</th>
                                </tr>
                                </thead>
                                <tbody>

                                    <td>{{$attendance->id}}</td>
                                    <td>{{ $attendance['school']['name'] }}</td>

                                    @if($attendance['student_id'] != '')
                                    <td>{{ $attendance['student']['name'] }}</td>
                                    @else
                                        @if($attendance['staff_id'] != '')
                                            <td>{{ $attendance['staff_name'] }}</td>
                                        @endif
                                    @endif

                                @if($attendance['student_id'] != '')

                                    <td>{{ $attendance['Class_Master']['name'] }}</td>

                                    <td>{{ $attendance['class_division'] }}</td>

                                @endif

                                    <td>{{ $attendance['school_in_time'] }}</td>

                                    <td>{{ $attendance['school_out_time'] }}</td>

                                    <td>{{ $attendance['attendance_date'] }}</td>

                                    @if($attendance['on_leave'] == 1)
                                        <td>Yes</td>
                                    @else
                                        <td>No</td>
                                    @endif

                                    <td>{{ $attendance['attendance_time'] }}</td>

                                </tbody>
                            </table>
                            <hr>
                            <input type="hidden" name="view" id="view" value="0">
                            <button class="btn btn-info" type="button" name="viewmore" onclick="view_more()" id="viewmore">View More</button>
                            <hr>
                            <div  id="view_table" style="display: none;">

                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                        <tr>
                                            <th>Device</th>
                                            <th>Attendance At</th>
                                            <th>In-Time</th>
                                            <th>Out-Time</th>
                                            <th>Map</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($attendance['AttendanceDetail'] as $key => $value)
                                            <tr>
                                            <?php $device= \App\Device::where('id',$value['device_id'])->first();?>
                                            @if(!empty($device))
                                                <td>{{ $device->name }}</td>
                                            @else
                                                <td></td>
                                            @endif
                                            <td>{{ $value['attendance_time'] }}</td>

                                            <td>{{ $value['school_in_time'] }}</td>

                                            <td>{{ $value['school_out_time'] }}</td>
                                             @if(!empty($device) && $device->device_type == 'vehicle')
                                                 <td><button type="button" class="btn btn-info" onclick="view_map('{{$value['latittude']}}','{{$value['longitude']}}')"><i class="fa fa-map"></i></button></td>
                                             @else
                                                 <td>{{$value['device_id']}}</td>
                                             @endif
                                        </tr>
                                        @endforeach

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.row -->
        </section>
        <div id="map-canvas" style="display: none"></div>
        <!-- /.content -->
    </div>

<input type="hidden" name="lat" id="lat" value="0">
<input type="hidden" name="long" id="long" value="0">

    <style>
        #map-canvas {
            height: 300px;
            padding: 0px;
            margin: 0px 20px 0px 20px;
            width:auto;
        }
    </style>
    <script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyC_gaCbVqzGG297j2622AwQhQ7S_o4IlXs" type="text/javascript"></script>

    <script>
        function view_more(){
            var view = document.getElementById('view').value;
            if(view == 0){
                document.getElementById('view_table').style.display="block";
                document.getElementById('viewmore').innerHTML="Hide More";
                document.getElementById('view').value = 1;
            }
            else{
                document.getElementById('view_table').style.display="none";
                document.getElementById('viewmore').innerHTML="View More";
                document.getElementById('view').value=0;
            }
        }

        function view_map(lat,long){
            document.getElementById('map-canvas').style.display="block";
            document.getElementById('lat').value=lat;
            document.getElementById('long').value=long;

            var latval = $('input[id=lat]').val();
            var lngval = $('input[id=long]').val();

            var mapOptions = {
                zoom: 15,
                center: new google.maps.LatLng(latval, lngval),
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };
            var marker = new google.maps.Marker({
                position: new google.maps.LatLng(latval, lngval),
                map: map,
                title: 'Your Child is here!'
            });
            var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
            marker.setMap(map);
        }
    </script>

@endsection
