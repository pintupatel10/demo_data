@extends('layouts.app')
@section('content')

    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                {{ $menu }}
                <small>Management</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">{{ $menu }}</li>
            </ol>

            <br>
            @include ('error')

            <div class="box">

                <div class="box-header">
                    <h3 class="box-title"><a href="{{ url('/calendar/create/') }}" ><button class="btn bg-orange margin" type="button">Add Holiday</button></a></h3>
                    <a title="refresh" href="{{ url('calendar') }}" ><button class="btn btn-info" type="button"><span style="padding:3px;" class="fa fa-refresh"></span></button></a>

                    <div class="col-md-3 col-xs-12 pull-right" style="margin-top: 10px;">
                        {!! Form::open(['url' => url('calendar'), 'method' => 'get', 'class' => 'form-horizontal','files'=>false]) !!}
                        <div class="form-group">
                            <span class="col-sm-9 col-xs-9">
                        <input  class="form-control" type="text" @if(!empty($search)) value="{{$search}}" @else placeholder="Search" @endif name="search" id="search">
                            </span>
                        <span class=" col-sm-3 col-xs-3">
                            <button class="btn btn-info pull-right" type="submit">Search</button>
                        </span>
                        </div>
                        {!! Form::close() !!}
                    </div>

                </div>


                <!-- /.box-header -->
                <div class="box-body table-responsive">
                    <table id="" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>Id</th>
                            <th>School Name</th>
                            <th>Holiday Name</th>
                            <th>Holiday Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($calendar as $list)
                            <tr>
                                <td>{{ $list['id'] }}</td>
                                <td>{{ $list['calendar_school']['name'] }}</td>
                                <td>{{ $list['holiday_name'] }}</td>
                                <td>{{ $list['holiday_date'] }}</td>
                                <td>{!!  $list['status']=='active'? '<span class="label label-success">Active</span>' : '<span class="label label-danger">In-active</span>' !!}</td>
                                <td><div class="btn-group-horizontal">

                                        {{ Form::open(array('route' => array('calendar.show', $list['id']), 'method' => 'get','style'=>'display:inline')) }}
                                        <button class="btn btn-info" type="submit" ><i class="fa fa-eye"></i></button>
                                        {{ Form::close() }}

                                        {{ Form::open(array('route' => array('calendar.edit', $list['id']), 'method' => 'get','style'=>'display:inline')) }}
                                        <button class="btn btn-success" type="submit" ><i class="fa fa-edit"></i></button>
                                        {{ Form::close() }}
                                        <button class="btn btn-danger" type="button" data-toggle="modal" data-target="#myModal{{$list['id']}}"><i class="fa fa-trash"></i></button>

                                    </div></td>

                            </tr>

                            <div id="myModal{{$list['id']}}" class="fade modal modal-danger" role="dialog" >
                                {{ Form::open(array('route' => array('calendar.destroy', $list['id']), 'method' => 'delete','style'=>'display:inline')) }}
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span></button>
                                            <h4 class="modal-title">Delete Calendar</h4>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to delete Calendar Holiday ?</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline pull-left" data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-outline">Delete</button>
                                        </div>
                                    </div>
                                    <!-- /.modal-content -->
                                </div>
                                {{ Form::close() }}
                            </div>

                        @endforeach
                    </table>
                    <div style="text-align:right;float:right;"> @include('pagination.limit_links', ['paginator' => $calendar])</div>

                </div>
                <!-- /.box-body -->
            </div>

 <!-- Modal -->


        </section>

        <!-- Main content -->

        <!-- /.content -->
    </div>


@endsection