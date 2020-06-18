@extends('layouts.app')
@section('content')
<style>

    td{
        padding: 5px;
        text-align: center;
    }
    th{
        padding: 4px;

    }
</style>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Dashboard
            <small>Control panel</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Dashboard</li>
        </ol>
    </section>

    @if(Auth::user()->role != 'parent' && Auth::user()->staff_role != 'peon')

        {!! Form::open(['url' => url('dashboard'),'method'=>'get', 'class' => 'form-horizontal']) !!}
        <div class="box-body">
            <label class="col-sm-1 col-xs-12 control-label" for="from">Date <span class="text-red"></span></label>
            <div class="col-sm-4 col-xs-8">
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                    {!! Form::text('date', \Illuminate\Support\Facades\Input::get('date')?\Illuminate\Support\Facades\Input::get('date'):\Carbon\Carbon::today()->format('Y-m-d'), ['class' => 'form-control', 'id'=>'datepicker2']) !!}
                </div>
                @if ($errors->has('date'))
                    <span class="help-block">
                     <strong style="color: #dd4b39;">{{ $errors->first('date') }}</strong>
                      </span>
                @endif
            </div>
            <button  class="btn btn-info" type="submit"></i>Submit</button>

        </div>
        {!! Form::close() !!}
@endif
    <!-- Main content -->
    <section class="content">
        <div class="row">
            @if(Auth::user()->role == 'admin')
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3> {{ count($schools) }}</h3>
                        <p><b>Total Schools</b></p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-university"></i>
                    </div>
                    <a href="{{URL::to('school')}}" class="small-box-footer">View Schools <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            @endif
                @if(Auth::user()->role == 'admin' || Auth::user()->role == 'staff')

                <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-purple">

                    <div class="inner">
                        <h3>{{ $parents }}</h3>
                        <p><b>Total Parents</b></p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-person"></i>
                    </div>
                    <a href="{{URL::to('parents')}}" class="small-box-footer">Parents<i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-green">

                    <div class="inner">
                        <h3>{{ $students }}</h3>
                        <p><b>Total Students</b></p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-graduation-cap"></i>
                    </div>
                    <a href="{{URL::to('student')}}" class="small-box-footer">Students<i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
                @endif

            @if(Auth::user()->role == 'admin' || Auth::user()->staff_role == 'principal')
                <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-blue">

                    <div class="inner">
                        <h3>{{ $staff }}</h3>
                        <p><b>Total Staff</b></p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-users"></i>
                    </div>
                    <a href="{{URL::to('staff')}}" class="small-box-footer">Staff<i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
             @endif

                @if(Auth::user()->role == 'parent')

                    <div class="col-lg-3 col-xs-6">
                        <!-- small box -->
                        <div class="small-box bg-yellow">

                            <div class="inner">
                                <h3>{{ $child }}</h3>
                                <p><b>Total Child</b></p>
                            </div>
                            <div class="icon">
                                <i class="fa fa-child"></i>
                            </div>
                            <a href="{{URL::to('child')}}" class="small-box-footer">View Child Info<i class="fa fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                @endif
        </div>

        <div class="row">
            @foreach($schools as $school)
                @if(Auth::user()->role == 'admin' || Auth::user()->role == 'staff')
                @if(Auth::user()->role == 'admin' || Auth::user()->school_id == $school->id)
            <div class="col-lg-3 col-xs-12">
                <!-- small box -->
                <div class="small-box bg-yellow">

                    <div class="inner">
                        <p><b>{{ $school->name }}</b></p>
                        <p><b>{{ $school->class }} Class</b></p>
                        {{--<p><b>{{ $school->student }} Total Students</b></p>--}}
                        {{--<p><b>{{ $school->stud_present }} Present / {{ $school->stud_absent }} Absent</b></p>--}}<br>
                        <div class="table-responsive">

                            <table class="table">
                                <thead>
                                <tr>
                                <th></th>
                                <th> Present </th>
                                <th> Absent </th>
                                <th> Leave </th>
                                </tr>
                                </thead>
                                <tbody>

                                <tr>
                                    <th>Student [{{ $school->student }}]</th>
                                    <td>{{ $school['student_present'] }}</td>
                                    <td>{{ $school['student_absent'] }}</td>
                                    <td>{{ $school['student_leave'] }}</td>
                                </tr>

                                @if(Auth::user()->role == 'admin' || Auth::user()->staff_role == 'principal')

                                    <tr>
                                    <th>Staff [{{ $school->staff }}]</th>
                                    <td>{{ $school['staff_present'] }}</td>
                                    <td>{{ $school['staff_absent'] }}</td>
                                    <td>{{ $school['staff_leave'] }}</td>
                                </tr>
                                <tr>
                                    <th>Teacher [{{ $school->teacher }}]</th>
                                    <td>{{ $school['teacher_present'] }}</td>
                                    <td>{{ $school['teacher_absent'] }}</td>
                                    <td>{{ $school['teacher_leave'] }}</td>
                                </tr>
                                <tr>
                                    <th>Accountant [{{ $school->accountant }}]</th>
                                    <td>{{ $school['accountant_present'] }}</td>
                                    <td>{{ $school['accountant_absent'] }}</td>
                                    <td>{{ $school['accountant_leave'] }}</td>
                                </tr>
                                <tr>
                                    <th>Peon [{{ $school->peon }}]</th>
                                    <td>{{ $school['peon_present'] }}</td>
                                    <td>{{ $school['peon_absent'] }}</td>
                                    <td>{{ $school['peon_leave'] }}</td>
                                </tr>
                                @endif

                                </tbody>
                            </table>
                        </div>

                        {{--<p><b> Total Staff :{{ $school->staff }}/ Present: {{ $school['staff_present'] }}  / Leave : {{ $school['staff_leave'] }}  / Absent : {{ $school['staff_absent'] }} </b></p>--}}
                        {{--<p><b>  Teacher :{{ $school->teacher }}/ Present: {{ $school['teacher_present'] }}  / Leave : {{ $school['teacher_leave'] }}  / Absent : {{ $school['teacher_absent'] }} </b></p>--}}
                        {{--<p><b>  Accountant :{{ $school->accountant }}/ Present: {{ $school['accountant_present'] }}  / Leave : {{ $school['accountant_leave'] }}  / Absent : {{ $school['accountant_absent'] }} </b></p>--}}
                        {{--<p><b>  Peon :{{ $school->peon }}/ Present: {{ $school['peon_present'] }}  / Leave : {{ $school['peon_leave'] }}  / Absent : {{ $school['peon_absent'] }} </b></p>--}}

                    </div>
                    <div class="icon">
                        <i class="fa fa-university"></i>
                    </div>
                    <a href="{{URL::to('attendance?school_id='.$school->id.'&date='.\Carbon\Carbon::today()->format('Y-m-d').'&submit=submit')}}" class="small-box-footer">View<i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            @endif
            @endif
            @endforeach

        </div>

    </section>
    <!-- /.content -->
</div>
@endsection