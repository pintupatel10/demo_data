@extends('layouts.app')
@section('content')
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
                <div class="col-md-6">
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title">View Attendance Detail</h3>
                        </div>
                        <div class="box-body">
                            <table class="table table-bordered">
                                <tbody>
                                <tr>
                                    <th>Id</th>
                                    <td>{{$attendance->id}}</td>
                                </tr>
                                <tr>
                                    <th>School Name</th>
                                    <td>{{ $attendance['school']['name'] }}</td>

                                </tr>
                                <tr>
                                    <th>Student Name</th>
                                    <td>{{ $attendance['student']['name'] }}</td>
                                </tr>
                                <tr>
                                    <th>Class Name</th>
                                    <td>{{ $attendance['Class_Master']['name'] }}</td>
                                </tr>
                                <tr>
                                    <th>Division</th>
                                    <td>{{ $attendance['class_division'] }}</td>
                                </tr>
                                <tr>
                                    <th>In-Time</th>
                                    <td>{{ $attendance['school_in_time'] }}</td>
                                </tr>
                                <tr>
                                    <th>Out-Time</th>
                                    <td>{{ $attendance['school_out_time'] }}</td>
                                </tr>
                                <tr>
                                    <th>Attendance Date</th>
                                    <td>{{ $attendance['attendance_date'] }}</td>
                                </tr>

                                <tr>
                                    <th>Attendance Time</th>
                                    <td>{{ $attendance['attendance_time'] }}</td>
                                </tr>
                                <tr>
                                    <th>Staff Name</th>
                                    <td>{{ $attendance['staff_name'] }}</td>
                                </tr>
                                {{--<tr>--}}
                                    {{--<th>Status</th>--}}
                                    {{--<td>{!! $attendance['status']=='active'? '<span class="label label-success">Active</span>' : '<span class="label label-danger">In-active</span>' !!}</td>--}}
                                {{--</tr>--}}
                                <tr>
                                    <th>Created At</th>
                                    <td>{{$attendance->created_at}}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{$attendance->updated_at}}</td>
                                </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.row -->
        </section>
        <!-- /.content -->
    </div>
@endsection
