@extends('layouts.app')
@section('content')
    <div class="content-wrapper" style="min-height: 946px;">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                {{ $menu }}
                <small>Import</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home </a></li>
                <li><a href="#">{{ $menu }}</a></li>
                <li class="active">Import</li>
            </ol>

            <br>
            @include ('error')

        </section>

        <!-- Main content -->
        <section class="content">
            <div class="row">
                <!-- right column -->
                <div class="col-md-6">
                    <!-- Horizontal Form -->
                    <div class="box box-info">
                        <div class="box-header with-border">
                            <h3 class="box-title">IMPORT PARENTS</h3>
                        </div>
                        <!-- /.box-header -->
                        <!-- form start -->

                        {!! Form::open(['url' => url('/parents/import_data'), 'files'=>true, 'method' => 'post', 'class' => 'form-horizontal']) !!}

                            <div class="box-body">
                                <div class="form-group{{ $errors->has('parents_excel') ? ' has-error' : '' }}">
                                    <label class="col-sm-2 control-label" for="image">Excel File<span class="text-red">*</span></label>
                                    <div class="col-sm-10">
                                        <div class="">
                                            {!! Form::file('parents_excel', ['id'=> 'parents_excel']) !!}
                                        </div>
                                            @if ($errors->has('parents_excel'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('parents_excel') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <!-- /.box-body -->

                            <div class="box-footer">
                                <a href="{{ url('parents') }}" ><button class="btn btn-default" type="button">Back</button></a>
                                <button class="btn btn-info pull-right" type="submit">Upload</button>
                            </div>
                            <!-- /.box-footer -->
                        {!! Form::close() !!}

                    </div>
                </div>
                <!--/.col (right) -->
            </div>
            <!-- /.row -->
        </section>
        <!-- /.content -->
    </div>
@endsection
