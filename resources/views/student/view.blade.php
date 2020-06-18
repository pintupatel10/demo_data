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
                <li><a href="#"><i class="fa fa-dashboard"></i> Home </a></li>
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
                            <h3 class="box-title">View Student Detail</h3>
                        </div>
                        <div class="box-body">
                            <table class="table table-bordered">
                                <tbody>
                                <tr>
                                    <th>Student Roll No</th>
                                    <td>{{$student->roll_no}}</td>
                                </tr>
                                <tr>
                                    <th>School Id</th>
                                    <td>{{$student->student_school->name}}</td>
                                </tr>
                                <tr>
                                    <th>Class Id</th>
                                    <td>{{$student->student_class->name}}</td>
                                </tr>
                                <tr>
                                    <th>Student Division</th>
                                    <td>{{$student->division}}</td>
                                </tr>
                                <tr>
                                    <th>Student Name</th>
                                    <td>{{$student->name}}</td>
                                </tr>
                                <tr>
                                    <th>Student Address</th>
                                    <td>{{$student->address}}</td>
                                </tr>
                                <tr>
                                    <th>Student BirthDate</th>
                                    <td>{{$student->birthdate}}</td>
                                </tr>
                                <tr>
                                    <th>Student Blood Group</th>
                                    <td>{{$student->blood_group}}</td>
                                </tr>
                                <tr>
                                    <th>Student Parents Mobile No</th>
                                    <td>{{$student->mobile}}</td>
                                </tr>
                                <tr>
                                    <th>School Time</th>
                                    <td>{{$student->school_time}}</td>
                                </tr>
                                <tr>
                                    <th>Student Parents Name</th>
                                    <td>{{$student->parents_name}}</td>
                                </tr>
                                <tr>
                                    <th>Student Notes</th>
                                    <td>{{$student->notes}}</td>
                                </tr>
                                <tr>
                                    <th>Student RFID No</th>
                                    <td>{{$student->rfid_no}}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>{!! $student['status']=='active'? '<span class="label label-success">Active</span>' : '<span class="label label-danger">In-active</span>' !!}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{$student->created_at}}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{$student->updated_at}}</td>
                                </tr>

                                </tbody></table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.row -->
        </section>
        <!-- /.content -->
    </div>
@endsection
