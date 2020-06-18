

    <div class="box">

        <div class="box-body table-responsive">
            <table style="" id="" class="table table-bordered table-striped">
    <thead>
    <tr>
        <th>Role</th>
        <th>Name</th>
        <th>Email</th>
        <th>Reference Number</th>
        <th>Mobile</th>
        <th>School_time</th>
        <th>In-Time</th>
        <th>Out-Time</th>
        <th>Attendance</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($data as $list)
        <tr>
            <td>{{ $list['staff_role'] }}</td>
            <td>{{ $list['name'] }}</td>
            <td>{{ $list['email'] }}</td>
            <td>{{$list['rfid_no']}}</td>
            <td>{{ $list['mobile'] }}</td>
            <td>{{$list['school_time']}}</td>
            <td>{{ $list['In Time'] }}</td>
            <td>{{ $list['Out Time'] }}</td>
            <td>{{ $list['Attendance'] }}</td>
        </tr>
    @endforeach
    </tbody>
            </table>


        </div>
        <!-- /.box-body -->
    </div>

