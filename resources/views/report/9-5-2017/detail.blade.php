<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title">{{ $data['type'] }}</h3>
        <a href="{{url('report/export')}}"><button class="btn btn-info pull-right" type="button"><i class="fa fa-download"></i> Export</button></a>
    </div>

    <div class="box-body">
        <table style="" id="example2" class="table table-bordered table-striped">

            @if($data['type'] == 'Student')

                @if ($data['report'] == 'Attendance Today' || $data['report'] == 'Attendance By Date')

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
                    @foreach ($data['data'] as $list)
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

                @if ($data['report'] == 'Attendance For Parents' || $data['report'] == 'Attendance By Device' )
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
                    @foreach ($data['data'] as $list)
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


                @if ($data['report'] == 'Attendance By Month' || $data['report'] == 'Attendance By Year')

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
                    @foreach ($data['data'] as $list)
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
            @if($data['type']=='Staff')

                @if ($data['report'] == 'Attendance Today' || $data['report'] == 'Attendance By Date')
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
                    @foreach ($data['data'] as $list)
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

                @if ($data['report'] == 'Attendance By Month' || $data['report'] == 'Attendance By Year')

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
                    @foreach ($data['data'] as $list)
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

                    @if ($data['report'] == 'Attendance By Device' )
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
                    @foreach ($data['data'] as $list)
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
            @endif

        </table>
    </div>
    <!-- /.box-body -->
</div>