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
                            <h3 class="box-title">View Staff Detail</h3>
                        </div>
                        <div class="box-body">
                            <table class="table table-bordered">
                                <tbody>
                                <tr>
                                    <th>Staff Id</th>
                                    <td>{{$staff->id}}</td>
                                </tr>
                                <tr>
                                    <th>School Id</th>
                                    <td>{{$staff->staff_school->name}}</td>
                                </tr>
                                <tr>
                                    <th>Staff Name</th>
                                    <td>{{$staff->name}}</td>
                                </tr>
                                <tr>
                                    <th>Staff Address</th>
                                    <td>{{$staff->address}}</td>
                                </tr>
                                <tr>
                                    <th>Staff BirthDate</th>
                                    <td>{{$staff->birthdate}}</td>
                                </tr>
                                <tr>
                                    <th>Staff Blood Group</th>
                                    <td>{{$staff->blood_group}}</td>
                                </tr>
                                <tr>
                                    <th>Staff Mobile No</th>
                                    <td>{{$staff->mobile}}</td>
                                </tr>
                                <tr>
                                    <th>School Time</th>
                                    <td>{{$staff->school_time}}</td>
                                </tr>
                                <tr>
                                    <th>Staff Notes</th>
                                    <td>{{$staff->notes}}</td>
                                </tr>
                                <tr>
                                    <th>Staff RFID No</th>
                                    <td>{{$staff->rfid_no}}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>{!! $staff['status']=='active'? '<span class="label label-success">Active</span>' : '<span class="label label-danger">In-active</span>' !!}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{$staff->created_at}}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{$staff->updated_at}}</td>
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
