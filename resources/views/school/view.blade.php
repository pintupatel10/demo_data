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
                            <h3 class="box-title">View School Detail</h3>
                        </div>
                        <div class="box-body">
                            <table class="table table-bordered">
                                <tbody>
                                <tr>
                                    <th>Id</th>
                                    <td>{{$school->id}}</td>
                                </tr>
                                <tr>
                                    <th>School Name</th>
                                    <td>{{$school->name}}</td>
                                </tr>
                                <tr>
                                    <th>School Medium</th>
                                    <td>{{$school->medium}}</td>
                                </tr>
                                <tr>
                                    <th>School Type</th>
                                    <td>{{$school->type}}</td>
                                </tr>
                                <tr>
                                    <th>School Phone</th>
                                    <td>{{$school->phone}}</td>
                                </tr>
                                <tr>
                                    <th>School Mobile</th>
                                    <td>{{$school->mobile}}</td>
                                </tr>
                                <tr>
                                    <th>School Website</th>
                                    <td>{{$school->website}}</td>
                                </tr>
                                <tr>
                                    <th>School Website</th>
                                    <td><img src="{{ url($school->image) }}" width="150" style="padding-bottom:5px" ></td>
                                </tr>

                                <tr>
                                    <th>School Principal Name</th>
                                    <td>{{$school->principal_name}}</td>
                                </tr>
                                <tr>
                                    <th>School Trustee Name</th>
                                    <td>{{$school->trustee_name}}</td>
                                </tr>
                                <tr>
                                    <th>School Details</th>
                                    <td>{{$school->detail}}</td>
                                </tr>
                                <tr>
                                    <th>School Total strength</th>
                                    <td>{{$school->tot_strength}}</td>
                                </tr>
                                <tr>
                                    <th>School Start Time</th>
                                    <td>{{$school->start_time}}</td>
                                </tr>
                                <tr>
                                    <th>School End Time</th>
                                    <td>{{$school->end_time}}</td>
                                </tr>
                                <tr>
                                    <th>School Week Start Time</th>
                                    <td>{{$school->week_start_time}}</td>
                                </tr>
                                <tr>
                                    <th>School Week End Time</th>
                                    <td>{{$school->week_end_time}}</td>
                                </tr>
                                <tr>
                                    <th>School Refer By</th>
                                    <td>{{$school->refer_by}}</td>
                                </tr>
                                <tr>
                                    <th>School Market By</th>
                                    <td>{{$school->market_by}}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>{!! $school['status']=='active'? '<span class="label label-success">Active</span>' : '<span class="label label-danger">In-active</span>' !!}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{$school->created_at}}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{$school->updated_at}}</td>
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
