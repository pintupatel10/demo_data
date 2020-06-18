@extends('layouts.app')
<style>
    .select2-container .select2-selection--single {
        height: 34px !important;
    }

</style>
@section('content')
    <div class="content-wrapper" style="min-height: 946px;">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Child list</h3>
            </div>
            <div class="box-body table-responsive">
                <table id="example1" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Class Name</th>
                        <th>Division</th>
                        <th>In-Time</th>
                        <th>Out-Time</th>
                        <th>Attendance Date</th>
                        <th>Attendance Time</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($parent_child as  $parent_ch)

                        <?php
                             $child=\App\User::where('role','student')->where('id',$parent_ch['student_id'])->first();
                            $attendance = \App\Attendance::where('attendance_date',\Carbon\Carbon::today()->format('Y-m-d'))->where('student_id',$parent_ch->student_id)->first();
                        ?>
                        @if(!empty($attendance))

                            <tr>
                                <td>{{ $attendance['student']['name'] }}</td>
                                <td>{{ $attendance['Class_Master']['name'] }}</td>
                                <td>{{ $attendance['class_division'] }}</td>
                                <td>{{ $attendance['school_in_time'] }}</td>
                                <td>{{ $attendance['school_out_time'] }}</td>
                                <td>{{ $attendance['attendance_date'] }}</td>
                                <td>{{ $attendance['attendance_time'] }}</td>
                                @if($attendance->on_leave == 1)
                                    <td style="padding-top:15px;"><span class="label label-info">Leave</span></td>
                                @else
                                    <td style="padding-top:15px;"><span class="label label-success">Present</span></td>
                                @endif
                            </tr>

                        @else
                            <tr>
                                <td>{{ $child['name'] }}</td>
                                <?php $class=\App\Class_Master::where('id',$child->class_id)->first();?>
                                <td>{{ $class['name'] }}</td>
                                <td>{{ $child['division'] }}</td>
                                <td></td>
                                <td></td>
                                <td>{{\Carbon\Carbon::today()->format('Y-m-d')}}</td>
                                <td></td>
                                <td style="padding-top: 15px;"><span class="label label-danger">Absent</span></td>
                            </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection









