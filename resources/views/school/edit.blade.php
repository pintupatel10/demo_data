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
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
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
                            <h3 class="box-title">EDIT SCHOOL </h3>
                        </div>
                        <!-- /.box-header -->
                        <!-- form start -->
                        {!! Form::model($school, ['url' => url('school/' . $school->id), 'method' => 'patch', 'class' => 'form-horizontal','files'=>true]) !!}
                        <div class="box-body">
                            @include ('school.form')
                        </div>
                        <!-- /.box-body -->
                        <div class="box-footer">
                            <a href="{{ url('school') }}" ><button class="btn btn-default" type="button">Cancel</button></a>
                            <button class="btn btn-info" type="reset">Reset</button>
                            <button class="btn btn-info pull-right" type="submit">Update</button>
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
