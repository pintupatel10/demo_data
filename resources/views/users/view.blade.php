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
                            <h3 class="box-title">View User Detail</h3>
                        </div>
                        <div class="box-body">
                            <table class="table table-bordered">
                                <tbody>
                                <tr>
                                    <th>Id</th>
                                    <td>{{$user->id}}</td>
                                </tr>
                                <tr>
                                    <th>Name</th>
                                    <td>{{$user->name}}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>{{$user->login_name}}</td>
                                </tr>
                                <tr>
                                    <th>Role</th>
                                    <td>@if ($user['role']=="admin")   {!! '<a class="btn btn-success btn-flat btn-xs">Admin</a>' !!}  @elseif($user['role']=='event_admin') {!! '<a class="btn bg-purple btn-flat btn-xs">Event Admin</a>' !!} @elseif($user['role']=='operator') {!! '<a class="btn btn-danger btn-flat btn-xs">Operator</a>' !!} @endif </td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>{!!  $user['status']=='active'? '<span class="label label-success">Active</span>' : '<span class="label label-danger">In-active</span>' !!}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{$user->created_at}}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{$user->updated_at}}</td>
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
