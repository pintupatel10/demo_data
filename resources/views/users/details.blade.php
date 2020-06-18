@extends('layouts.app')
@section('content')

    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
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
                    <h3 class="box-title"><a href="{{ url('users/create/') }}" ><button class="btn bg-orange margin" type="button">Add User</button></a></h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>Id</th>
                            <th>Username</th>
                            <th>Email</th>
                            {{--<th>Status</th>--}}
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>

                        @foreach ($list as $list)

                            <tr>
                                <td>{{ $list['id'] }}</td>
                                <td>{{ $list['name'] }}</td>
                                <td>{{ $list['email'] }}</td>
                                {{--<td>@if ($list['role']=="admin")   {!! '<a class="btn btn-success btn-flat btn-xs">Admin</a>' !!}  @elseif($list['role']=='event_admin') {!! '<a class="btn bg-purple btn-flat btn-xs">Event Admin</a>' !!} @elseif($list['role']=='operator') {!! '<a class="btn btn-danger btn-flat btn-xs">Operator</a>' !!} @endif  </td>--}}
                                {{--<td>{!!  $list['status']=='active'? '<span class="label label-success">Active</span>' : '<span class="label label-danger">In-active</span>' !!}</td>--}}

                                {{--<td>--}}
                                    {{--@if($list['status'] == 'active')--}}
                                        {{--<div class="btn-group-horizontal" id="assign_remove_{{ $list['id'] }}" >--}}
                                            {{--<button class="btn btn-success unassign ladda-button" data-style="slide-left" id="remove" ruid="{{ $list['id'] }}"  type="button"><span class="ladda-label">Active</span> </button>--}}
                                        {{--</div>--}}
                                        {{--<div class="btn-group-horizontal" id="assign_add_{{ $list['id'] }}"  style="display: none"  >--}}
                                            {{--<button class="btn btn-danger assign ladda-button" data-style="slide-left" id="assign" uid="{{ $list['id'] }}"  type="button"><span class="ladda-label">De-active</span></button>--}}
                                        {{--</div>--}}
                                    {{--@endif--}}
                                    {{--@if($list['status'] == 'inactive')--}}
                                        {{--<div class="btn-group-horizontal" id="assign_add_{{ $list['id'] }}"   >--}}
                                            {{--<button class="btn btn-danger assign ladda-button" id="assign" data-style="slide-left" uid="{{ $list['id'] }}"  type="button"><span class="ladda-label">De-active</span></button>--}}
                                        {{--</div>--}}
                                        {{--<div class="btn-group-horizontal" id="assign_remove_{{ $list['id'] }}" style="display: none" >--}}
                                            {{--<button class="btn  btn-success unassign ladda-button" id="remove" ruid="{{ $list['id'] }}" data-style="slide-left"  type="button"><span class="ladda-label">Active</span></button>--}}
                                        {{--</div>--}}
                                    {{--@endif--}}
                                {{--</td>--}}
                                <td><div class="btn-group-horizontal">

                                        {{--{{ Form::open(array('route' => array('users.show', $list['id']), 'method' => 'get','style'=>'display:inline')) }}--}}
                                        {{--<button class="btn btn-info" type="submit" ><i class="fa fa-eye"></i></button>--}}
                                        {{--{{ Form::close() }}--}}

                                        {{ Form::open(array('url' =>'users/'.$list['id'].'/edit', 'method' => 'get','style'=>'display:inline')) }}
                                        <button class="btn btn-success" type="submit" ><i class="fa fa-edit"></i></button>
                                        {{ Form::close() }}

                                        @if ($list['id'] != Auth::user()->id)
                                        <button class="btn btn-danger" type="button" data-toggle="modal" data-target="#myModal{{$list['id']}}"><i class="fa fa-trash"></i></button>
                                        @endif
                                    </div></td>
                            </tr>

                            <div id="myModal{{$list['id']}}" class="fade modal modal-danger" role="dialog" >
                                {{ Form::open(array('url' =>'users/'.$list['id'], 'method' => 'delete','style'=>'display:inline')) }}
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span></button>
                                            <h4 class="modal-title">Delete User</h4>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to delete this user ?</p>
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
                </div>
                <!-- /.box-body -->
            </div>



        </section>

        <!-- Main content -->

        <!-- /.content -->
    </div>
@endsection

<script src="{{ URL::asset('assets/jquery.js')}}"></script>
<link rel="stylesheet" href="{{ URL::asset('assets/plugins/ladda/ladda-themeless.min.css')}}">
<script src="{{ URL::asset('assets/plugins/ladda/spin.min.js')}}"></script>
<script src="{{ URL::asset('assets/plugins/ladda/ladda.min.js')}}"></script>
<script>Ladda.bind( 'input[type=submit]' );</script>
<script type="text/javascript">
    $(document).ready(function(){
        $('.assign').click(function(){

            var user_id = $(this).attr('uid');
            var l = Ladda.create(this);
            l.start();
            $.ajax({
                url: '{{url('api/cms/user/assign')}}',
                type: "put",
                data: {'id': user_id,'X-CSRF-Token' : $('meta[name=_token]').attr('content')},
                success: function(data){
                    l.stop();
                    $('#assign_remove_'+user_id).show();
                    $('#assign_add_'+user_id).hide();
                }
            });
        });

        $('.unassign').click(function(){
            //alert('in');
            var user_id = $(this).attr('ruid');
            var l = Ladda.create(this);
            l.start();
            $.ajax({
                url: '{{url('api/cms/user/unassign')}}',
                type: "put",
                data: {'id': user_id,'X-CSRF-Token' : $('meta[name=_token]').attr('content')},
                success: function(data){
                    l.stop();
                    $('#assign_remove_'+user_id).hide();
                    $('#assign_add_'+user_id).show();
                }
            });
        });
    });


</script>