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
                            <h3 class="box-title">View Class Detail</h3>
                        </div>
                        <div class="box-body">
                            <table class="table table-bordered">
                                <tbody>
                                <tr>
                                    <th>Id</th>
                                    <td>{{$calendar->id}}</td>
                                </tr>
                                <tr>
                                    <th>School Id</th>
                                    <td>{{$calendar->calendar_school->name}}</td>
                                </tr>
                                <tr>
                                    <th>Holiday Name</th>
                                    <td>{{$calendar->holiday_name}}</td>
                                </tr>
                                <tr>
                                    <th>Holiday Date</th>
                                    <td>{{$calendar->holiday_date}}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>{!! $calendar['status']=='active'? '<span class="label label-success">Active</span>' : '<span class="label label-danger">In-active</span>' !!}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{$calendar->created_at}}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{$calendar->updated_at}}</td>
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
