<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title">{{ $type }}</h3>
        <button  class="btn btn-info pull-right" type="button" onclick="get_export('pdf')"><i class="fa fa-download"></i>  PDF </button>
        <button  class="btn btn-info pull-right" type="button" onclick="get_export('xlsx')"><i class="fa fa-download"></i>  Excel </button>

    </div>

    <div class="box-body">
        <table style="" id="" class="table table-bordered table-striped">

            @if($type == 'Student')

                @if ($report == 'Attendance Today' || $report == 'Attendance By Date')

                    <thead>
                    <tr>
                        <th>Id</th>
                        <th>Name</th>
                        <th>Mobile</th>
                        <th>School</th>
                        <th>Class</th>
                        <th>Division</th>
                        <th>School_time</th>
                        <th>In-Time</th>
                        <th>Out-Time</th>
                        <th>Attendance</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($data as $list)
                        <tr>
                            <td>{{ $list['id'] }}</td>
                            <td>{{ $list['name'] }}</td>
                            <td>{{ $list['mobile'] }}</td>
                            <td>{{ $list['School'] }}</td>
                            <td>{{ $list['Class'] }}</td>
                            <td>{{ $list['division'] }}</td>
                            <td>{{$list['school_time']}}</td>
                            <td>{{ $list['In Time'] }}</td>
                            <td>{{ $list['Out Time'] }}</td>
                            <td>{{ $list['Attendance'] }}</td>

                        </tr>
                    @endforeach
                    </tbody>
                @endif

                    @if ($report == 'Attendance By Device' )
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Date</th>
                            <th>In-Time</th>
                            <th>Out-Time</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($data as $list)

                            <tr>
                                <td>{{$name}}</td>
                                <td>{{ $list['attendance_date'] }}</td>
                                <td>{{ $list['school_in_time'] }}</td>
                                <td>{{ $list['school_out_time'] }}</td>
                                <td>{{ $list['latittude'] }}</td>
                                <td>{{ $list['longitude'] }}</td>

                            </tr>
                        @endforeach
                        </tbody>
                    @endif

                @if ($report == 'Attendance For Parents')
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Attendance</th>
                        <th>Date</th>
                        <th>In-Time</th>
                        <th>Out-Time</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($data as $list)
                        <tr>
                            <td>{{$list['name']}}</td>
                            <td>{{ $list['Attendance'] }}</td>
                            <td>{{ $list['Date'] }}</td>
                            <td>{{ $list['In Time'] }}</td>
                            <td>{{ $list['Out Time'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                @endif


                @if ($report == 'Attendance By Month' || $report == 'Attendance By Year')

                    <thead>
                    <tr>
                        <th>Id</th>
                        <th>Name</th>
                        <th>Mobile</th>
                        <th>School</th>
                        <th>Class</th>
                        <th>Division</th>
                        <th>School_time</th>
                        <th>Working Days</th>
                        <th>Present</th>
                        <th>Absent</th>
                        <th>Leaves</th>
                        <th>Holidays</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($data as $list)
                        <tr>
                            <td>{{ $list['id'] }}</td>
                            <td>{{ $list['name'] }}</td>
                            <td>{{ $list['mobile'] }}</td>
                            <td>{{ $list['School'] }}</td>
                            <td>{{ $list['Class'] }}</td>
                            <td>{{ $list['division'] }}</td>
                            <td>{{$list['school_time']}}</td>
                            <td>{{ $list['Working Days'] }}</td>
                            <td>{{ $list['Present'] }}</td>
                            <td>{{ $list['Absent'] }}</td>
                            <td>{{ $list['Leaves'] }}</td>
                            <td>{{ $list['Holidays'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                @endif
            @endif
            @if($type=='Staff')

                @if ($report == 'Attendance Today' || $report == 'Attendance By Date')
                    <thead>
                    <tr>
                        <th>Id</th>
                        <th>Name</th>
                        <th>Mobile</th>
                        <th>Email</th>
                        <th>School</th>
                        <th>School_time</th>
                        <th>In-Time</th>
                        <th>Out-Time</th>
                        <th>Attendance</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($data as $list)
                        <tr>
                            <td>{{ $list['id'] }}</td>
                            <td>{{ $list['name'] }}</td>
                            <td>{{ $list['mobile'] }}</td>
                            <td>{{ $list['email'] }}</td>
                            <td>{{ $list['School'] }}</td>
                            <td>{{$list['school_time']}}</td>
                            <td>{{ $list['In Time'] }}</td>
                            <td>{{ $list['Out Time'] }}</td>
                            <td>{{ $list['Attendance'] }}</td>

                        </tr>
                    @endforeach
                    </tbody>
                @endif

                @if ($report == 'Attendance By Month' || $report == 'Attendance By Year')

                    <thead>
                    <tr>
                        <th>Id</th>
                        <th>Name</th>
                        <th>Mobile</th>
                        <th>School</th>
                        <th>School_time</th>
                        <th>Working Days</th>
                        <th>Present</th>
                        <th>Absent</th>
                        <th>Leaves</th>
                        <th>Holidays</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($data as $list)
                        <tr>
                            <td>{{ $list['id'] }}</td>
                            <td>{{ $list['name'] }}</td>
                            <td>{{ $list['mobile'] }}</td>
                            <td>{{ $list['School'] }}</td>
                            <td>{{$list['school_time']}}</td>
                            <td>{{ $list['Working Days'] }}</td>
                            <td>{{ $list['Present'] }}</td>
                            <td>{{ $list['Absent'] }}</td>
                            <td>{{ $list['Leaves'] }}</td>
                            <td>{{ $list['Holidays'] }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                @endif

                    @if ($report == 'Attendance By Device' )
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Date</th>
                            <th>In-Time</th>
                            <th>Out-Time</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($data as $list)

                            <tr>
                                <td>{{$name}}</td>
                                <td>{{ $list['attendance_date'] }}</td>
                                <td>{{ $list['school_in_time'] }}</td>
                                <td>{{ $list['school_out_time'] }}</td>
                                <td>{{ $list['latittude'] }}</td>
                                <td>{{ $list['longitude'] }}</td>

                            </tr>
                        @endforeach
                        </tbody>
                    @endif
            @endif
        </table>
        @if(isset($data))
        <div style="text-align:right;float:right;"> @include('pagination.limit_links', ['paginator' => $data])</div>
        @endif

    </div>
    <!-- /.box-body -->
</div>
<script>
   function get_export(export_type){
       var page ='report/export?';
       var queryString = $("#form1").serialize();
       var url=page+queryString+'&export=export'+'&export_type='+export_type;
       window.location = url;
    }
</script>