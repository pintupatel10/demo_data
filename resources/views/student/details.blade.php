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
                <li><a href="#"><i class="fa fa-dashboard"></i> Home </a></li>
                <li class="active">{{ $menu }}</li>
            </ol>

            <br>
            @include ('error')

            <div class="box">

                <div class="box-header">
                    {{--<div class="col-md-6 col-xs-12">--}}
                    <h3 class="box-title">
                        <a href="{{ url('/student/create/') }}" ><button class="btn bg-orange margin" type="button">Add Student</button>
                        </a><a href="{{ url('/student/import') }}" ><button class="btn bg-orange margin" type="button">Import Student</button></a>
                        <a href="{{ url('/student/export') }}" ><button class="btn bg-orange margin">Export Students</button></a>
                        <a href="{{ url('/assets/sample/student.xlsx') }}" ><button class="btn bg-orange margin" type="button">Sample Excel</button></a>
                        <a style="margin: 10px;" title="refresh" href="{{ url('student') }}" ><button class="btn btn-info" type="button"><span style="padding:3px;" class="fa fa-refresh"></span></button></a>
                    </h3>

                    {{--</div>--}}

                    <div class="col-md-3 col-xs-12 pull-right" style="margin-top: 10px;">
                        {!! Form::open(['url' => url('student'), 'method' => 'get', 'class' => 'form-horizontal','files'=>false]) !!}
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
                    <div id="spinningSquaresG">
                        <div id="spinningSquaresG_1" class="spinningSquaresG"></div>
                        <div id="spinningSquaresG_2" class="spinningSquaresG"></div>
                        <div id="spinningSquaresG_3" class="spinningSquaresG"></div>
                        <div id="spinningSquaresG_4" class="spinningSquaresG"></div>
                        <div id="spinningSquaresG_5" class="spinningSquaresG"></div>
                        <div id="spinningSquaresG_6" class="spinningSquaresG"></div>
                        <div id="spinningSquaresG_7" class="spinningSquaresG"></div>
                        <div id="spinningSquaresG_8" class="spinningSquaresG"></div>
                    </div>
                    <table style="visibility: hidden" id="loader" class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>Roll No</th>
                            <th>Student Name</th>
                            <th>School</th>
                            <th>Class</th>
                            <th>Division</th>
                            <th>Student Blood Group</th>
                            <th>Student Parents Name</th>
                            <th>Parent's Mobile</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($student as $list)
                            <tr>
                                <td>{{ $list['roll_no'] }}</td>
                                <td>{{ $list['name'] }}</td>
                                <td>{{$list['student_school']['name']}}</td>
                                <td>{{$list['student_class']['name']}}</td>
                                <td>{{$list['division']}}</td>
                                <td>{{ $list['blood_group'] }}</td>
                                <td>{{ $list['parents_name'] }}</td>
                                <td>{{ $list['mobile'] }}</td>

                                 {{--$parent_chid=\App\ParentChild::where('student_id',$list['id'])->first();--}}
                                {{--if(!empty($parent_chid)){--}}
                                    {{--$parent=\App\User::where('role','parent')->where('id',$parent_chid->parent_id)->first();--}}
                                    {{--}--}}
                                {{----}}
                                {{--@if(!empty($parent_chid && $parent))--}}
                                {{--<td>{{ $parent['name'] }}</td>--}}
                                {{--<td>{{ $parent['mobile'] }}</td>--}}
                                {{--@else--}}
                                    {{--<td></td>--}}
                                    {{--<td></td>--}}
                                {{--@endif--}}
                                <td>{!!  $list['status']=='active'? '<span class="label label-success">Active</span>' : '<span class="label label-danger">In-active</span>' !!}</td>
                                <td><div class="btn-group-horizontal">

                                        {{ Form::open(array('route' => array('student.show', $list['id']), 'method' => 'get','style'=>'display:inline')) }}
                                        <button class="btn btn-info" type="submit" ><i class="fa fa-eye"></i></button>
                                        {{ Form::close() }}

                                        {{ Form::open(array('route' => array('student.edit', $list['id']), 'method' => 'get','style'=>'display:inline')) }}
                                        <button class="btn btn-success" type="submit" ><i class="fa fa-edit"></i></button>
                                        {{ Form::close() }}
                                        <button class="btn btn-danger" type="button" data-toggle="modal" data-target="#myModal{{$list['id']}}"><i class="fa fa-trash"></i></button>

                                    </div></td>

                            </tr>

                            <div id="myModal{{$list['id']}}" class="fade modal modal-danger" role="dialog" >
                                {{ Form::open(array('route' => array('student.destroy', $list['id']), 'method' => 'delete','style'=>'display:inline')) }}
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span></button>
                                            <h4 class="modal-title">Delete Student</h4>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to delete Student ?</p>
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
                    <div style="text-align:right;float:right;"> @include('pagination.limit_links', ['paginator' => $student])</div>
                </div>
                <!-- /.box-body -->
            </div>

 <!-- Modal -->


        </section>

        <!-- Main content -->

        <!-- /.content -->
    </div>

@endsection
<script>
//    function search(val){
//        if(val.length>=2) {
//            setTimeout(function () {
//                window.location.href = "?search=" + val;
//            }, 2000);
//        }
//    }
</script>
