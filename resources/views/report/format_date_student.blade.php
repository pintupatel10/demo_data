

    <div class="box">

        <div class="box-body table-responsive">
            <table style="" id="" class="table table-bordered table-striped">
    <thead>
    <tr>
        <th>Roll No</th>
        <th>Name</th>
        <th>Class</th>
        <th>Division</th>
        {{--<th>Birthdate</th>--}}
        <th>Reference Number</th>
        <th>Parents Name</th>
        <th>Mobile</th>
        {{--<th>School</th>--}}
        <th>School_time</th>
        <th>In-Time</th>
        <th>Out-Time</th>
        <th>Attendance</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($data as $list)
        <tr>
            <td>{{ $list['roll_no'] }}</td>
            <td>{{ $list['name'] }}</td>
            <td>{{ $list['Class'] }}</td>
            <td>{{ $list['division'] }}</td>
            {{--<td>{{ $list['birthdate'] }}</td>--}}
            <td>{{$list['rfid_no']}}</td>

            <td>{{$list['parents_name']}}</td>
            <td>{{ $list['mobile'] }}</td>
            {{--<td>{{ $list['School'] }}</td>--}}
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

