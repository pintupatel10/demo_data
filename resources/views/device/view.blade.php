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
                            <h3 class="box-title">View Device Detail</h3>
                        </div>
                        <div class="box-body">
                            <table class="table table-bordered">
                                <tbody>
                                <tr>
                                    <th>Id</th>
                                    <td>{{$device->id}}</td>
                                </tr>
                                <tr>
                                    <th>Device Name</th>
                                    <td>{{$device->name}}</td>
                                </tr>
                                <tr>
                                    <th>Device Serail</th>
                                    <td>{{$device->serial_no}}</td>
                                </tr>
                                <tr>
                                    <th>Device Type</th>
                                    <td>{{$device->device_type}}</td>
                                </tr>
                                <tr>
                                    <th>Device Location</th>
                                    <td>{{$device->location}}</td>
                                </tr>
                                <tr>
                                    <th>Device Description</th>
                                    <td>{{$device->description}}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>{!! $device['status']=='active'? '<span class="label label-success">Active</span>' : '<span class="label label-danger">In-active</span>' !!}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{$device->created_at}}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{$device->updated_at}}</td>
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
