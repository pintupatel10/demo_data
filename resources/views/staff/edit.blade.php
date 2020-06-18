@extends('layouts.app')
@section('content')
    <div class="content-wrapper" style="min-height: 946px;">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                {{ $menu }}
                <small>Edit</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home </a></li>
                <li><a href="#">{{ $menu }}</a></li>
                <li class="active">Edit</li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="row">
                <!-- right column -->
                <div class="col-md-6">
                    <!-- Horizontal Form -->
                    <div class="box box-info">
                        <div class="box-header with-border">
                            <h3 class="box-title">EDIT STAFF </h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="pad margin no-print">
                            <div style="margin-bottom: 0!important;" class="callout callout-info">
                                <h4><i class="fa fa-info"></i> Note:</h4>
                                Leave <strong>Password</strong> and <strong>Confirm Password</strong> empty if you are not going to change the password.
                            </div>
                        </div>

                        <!-- form start -->
                        {!! Form::model($staff, ['url' => url('staff/' . $staff->id), 'method' => 'patch', 'class' => 'form-horizontal','files'=>true]) !!}
                        <div class="box-body">
                            @include ('staff.form')
                        </div>
                        <!-- /.box-body -->
                        <div class="box-footer">
                            <a href="{{ url('staff') }}" ><button class="btn btn-default" type="button">Back</button></a>
                            <button class="btn btn-info pull-right" type="submit">Edit</button>
                        </div>
                        <!-- /.box-footer -->
                        {{ Form::close() }}
                    </div>
                </div>
                <!--/.col (right) -->
            </div>
            <!-- /.row -->
        </section>
        <!-- /.content -->
    </div>
@endsection
