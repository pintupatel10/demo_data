<?php

namespace App\Http\Controllers;

use App\Attendance;
use App\AttendanceDetail;
use App\Calendar;
use App\Class_Master;
use App\Device;
use App\Division;
use App\School;
use App\Student;
use App\User;
use Carbon\Carbon;
use Illuminate\Contracts\Redis\Database;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('staff');
    }

    const REPORT_FOR_STAFF = 'Staff';
    const REPORT_FOR_STUDENT = 'Student';
    const REPORT_TODAY = 'Attendance Today';
    const REPORT_DATE = 'Attendance By Date';
    const REPORT_PARENT = 'Attendance For Parents';
    const REPORT_MONTH = 'Attendance By Month';
    const REPORT_YEAR = 'Attendance By Year';
    //const REPORT_CLASS = 'Attendance By Class';
    const REPORT_DEVICE = 'Attendance By Device';


    public static $reports_student = [
        self:: REPORT_TODAY => 'Attendance Today',
        self:: REPORT_DATE => 'Attendance By Date',
        self:: REPORT_PARENT => 'Attendance For Parents',
        self:: REPORT_MONTH => 'Attendance By Month',
        self:: REPORT_YEAR => 'Attendance By Year',
        //self:: REPORT_CLASS => 'Attendance By Class',
        self:: REPORT_DEVICE => 'Attendance By Device',

    ];

    public static $report_for = [
        self:: REPORT_FOR_STAFF => 'Staff',
        self:: REPORT_FOR_STUDENT => 'Student',
    ];

    public static $reports_staff = [
        self:: REPORT_TODAY => 'Attendance Today',
        self:: REPORT_DATE => 'Attendance By Date',
        self:: REPORT_MONTH => 'Attendance By Month',
        self:: REPORT_YEAR => 'Attendance By Year',
        self:: REPORT_DEVICE => 'Attendance By Device',
    ];

    public function index()
    {
        $data['menu'] = "Report";
        $data['reports_student'] = self::$reports_student;
        $data['reports_staff'] = self::$reports_staff;
        $data['school_name'] = School::lists('name','id')->all();
        $data['device'] = Device::lists('device_type','id')->all();
        // $data['class']=Class_Master::lists('name','id')->all();
        return view('report.index', $data);
    }

    public function get_report(Request $request)
    {
        $this->validate($request, [
            'report_for' => 'required',
        ]);
        $holiday=array();
        $calender = Calendar::where('school_id',$request['school_id'])->get();
        foreach($calender as $key10=>$value10){
            array_push($holiday,$value10->holiday_date);
        }
        // $input = $request->all();
        if($request['report_for'] == 'Student') {
            $this->validate($request, [
                'report_type' => 'required',
                'school_id' => 'required',
                'class_name'=>'required',
            ]);
            $date = Carbon::today()->toDateString();
            if($request['student_id'] != ''){
                $student = User::where('role', 'student')->where('id', $request['student_id'])->get();
            }
            else if ($request['school_id'] != '' && $request['class_name'] != '' && $request['class_division'] != '') {
                $student = User::where('role', 'student')->where('school_id', $request['school_id'])->where('class_id', $request['class_name'])->where('division', $request['class_division'])->get();

            } else if ($request['school_id'] != '' && $request['class_name'] != '') {
                $student = User::where('role', 'student')->where('school_id', $request['school_id'])->where('class_id', $request['class_name'])->get();

            } else if ($request['school_id'] != '') {
                $student = Student::where('role', 'student')->where('school_id', $request['school_id'])->get();
            } else {
                $student = new \Illuminate\Database\Eloquent\Collection;
            }

            if ($request['report_type'] == 'Attendance Today' || $request['report_type'] == 'Attendance By Date') {
                if ($request['report_type'] == 'Attendance By Date') {
                    $this->validate($request, [
                        'date' => 'required',
                    ]);
                    $date = $request['date'];
                }

                $check_holiday=strtotime($date);

                if (date('N', $check_holiday) == 7 || in_array(date('Y-m-d',$check_holiday), $holiday)) {
                    Session::flash('message', 'There is Holiday on '.$date.', No records to export!');
                    Session::flash('alert-class', 'alert-danger');
                    Session::forget('data');
                    return redirect('report');
                }

                foreach ($student as $key => $value) {
                    $check_present = Attendance::where('student_id', $value->id)->where('attendance_date', $date)->first();
                    if (!empty($check_present)) {
                        if ($check_present->on_leave == 1) {
                            $student[$key]['Attendance'] = 'Leave';
                            $student[$key]['In Time'] = '';
                            $student[$key]['Out Time'] = '';
                        } else {
                            $student[$key]['Attendance'] = 'Present';
                            $student[$key]['In Time'] = $check_present->school_in_time;
                            $student[$key]['Out Time'] = $check_present->school_out_time;
                        }

                    } else {
                        $student[$key]['Attendance'] = 'Absent';
                        $student[$key]['In Time'] = '';
                        $student[$key]['Out Time'] = '';
                    }
                    $school_name = School::select('name')->where('id', $value['school_id'])->first();
                    $student[$key]['School'] = $school_name->name;
                    $class_name = Class_Master::select('name')->where('id', $value['class_id'])->first();
                    $student[$key]['Class'] = $class_name->name;
                    unset($student[$key]['school_id']);
                    unset($student[$key]['class_id']);
                    unset($student[$key]['created_at']);
                    unset($student[$key]['updated_at']);
                    unset($student[$key]['deleted_at']);
                    unset($student[$key]['session_token']);
                    unset($student[$key]['remember_token']);
                    unset($student[$key]['location']);
                    unset($student[$key]['language']);
                    unset($student[$key]['staff_role']);
                    unset($student[$key]['role']);
                    unset($student[$key]['status']);
                    unset($student[$key]['notes']);
                    unset($student[$key]['password']);
                    unset($student[$key]['deviceToken']);
                    unset($student[$key]['device_type']);
                }

                $data['type']='Student';
                $data['report']=$request['report_type'];
                $data['data']=$student;
                session(['data' => $data]);
            }

            if ($request['report_type'] == 'Attendance For Parents') {

                $this->validate($request, [
                    'school_id' => 'required',
                    'report_type' => 'required',
                    'student_id' => 'required',
                    'from' => 'required',
                    'to' => 'required',
                ]);

                $student = User::where('role', 'student')->where('id', $request['student_id'])->first();
                if (empty($student)) {
                    Session::flash('message', 'No Record Found to Export!');
                    Session::flash('alert-class', 'alert-danger');
                    return redirect('report');
                }
                $startTime = strtotime($request['from']);
                $endTime = strtotime($request['to']);
                for ($i = $startTime; $i <= $endTime; $i = $i + 86400) {
                    $Dates[] = date('Y-m-d', $i);
                }
                foreach ($Dates as $key => $date) {
                    $i = strtotime($date);
                    if (date('N', $i) <= 6 && !in_array(date('Y-m-d', $i), $holiday)) {
                        $check_present = Attendance::where('student_id', $request['student_id'])->where('attendance_date', $date)->first();
                        if (!empty($check_present)) {
                            if ($check_present->on_leave == 1) {
                                $data11[$key]['Date'] = $date;
                                $data11[$key]['Attendance'] = 'Leave';
                                $data11[$key]['In Time'] = '-----';
                                $data11[$key]['Out Time'] = '----';
                                $data11[$key]['name']=$student->name;
                            } else {
                                $data11[$key]['Date'] = $date;
                                $data11[$key]['Attendance'] = 'Present';
                                $data11[$key]['In Time'] = $check_present->school_in_time;
                                $data11[$key]['Out Time'] = $check_present->school_out_time;
                                $data11[$key]['name']=$student->name;

                            }

                        } else {
                            $data11[$key]['Date'] = $date;
                            $data11[$key]['Attendance'] = 'Absent';
                            $data11[$key]['In Time'] = '';
                            $data11[$key]['Out Time'] = '';
                            $data11[$key]['name']=$student->name;

                        }
                    } else {
                        $data11[$key]['Date'] = $date;
                        $data11[$key]['Attendance'] = 'Holiday';
                        $data11[$key]['In Time'] = '-----';
                        $data11[$key]['Out Time'] = '----';
                        $data11[$key]['name']=$student->name;
                    }
                }

                $data['type']='Student';
                $data['report']=$request['report_type'];
                $data['data']=$data11;
                session(['data' => $data]);
            }

            if ($request['report_type'] == 'Attendance By Month') {

                $this->validate($request, [
                    'month' => 'required',
                ]);

                $year = Carbon::parse($request['month'])->year;
                $month = Carbon::parse($request['month'])->month;

                foreach ($student as $key => $value) {
                    $working_days = 0;
                    $present = 0;
                    $absent = 0;
                    $leaves = 0;
                    $holiday_in_month = 0;
                    $start_date = "01-" . $month . "-" . $year;
                    $start_time = strtotime($start_date);

                    $end_time = strtotime("+1 month", $start_time);

                    for ($i = $start_time; $i < $end_time; $i += 86400) {
                        if (date('N', $i) <= 6 && !in_array(date('Y-m-d', $i), $holiday)) {
                            $working_days++;
                            $st_present = Attendance::where('attendance_date', date('Y-m-d', $i))->where('student_id', $value->id)->first();
                            if (!empty($st_present)) {
                                if ($st_present->on_leave == 1) {
                                    $leaves++;
                                } else {
                                    $present++;
                                }

                            } else {
                                $absent++;
                            }
                        } else {
                            $holiday_in_month++;
                        }
                    }

                    $student[$key]['Present'] = $present;
                    $student[$key]['Absent'] = $absent;
                    $student[$key]['Leaves'] = $leaves;
                    $student[$key]['Holidays'] = $holiday_in_month;
                    $student[$key]['Working Days'] = $working_days;

                    $school_name = School::select('name')->where('id', $value['school_id'])->first();
                    $student[$key]['School'] = $school_name->name;
                    $class_name = Class_Master::select('name')->where('id', $value['class_id'])->first();
                    $student[$key]['Class'] = $class_name->name;
                    unset($student[$key]['school_id']);
                    unset($student[$key]['class_id']);
                    unset($student[$key]['created_at']);
                    unset($student[$key]['updated_at']);
                    unset($student[$key]['deleted_at']);
                    unset($student[$key]['session_token']);
                    unset($student[$key]['remember_token']);
                    unset($student[$key]['location']);
                    unset($student[$key]['language']);
                    unset($student[$key]['staff_role']);
                    unset($student[$key]['role']);
                    unset($student[$key]['status']);
                    unset($student[$key]['notes']);
                    unset($student[$key]['password']);
                    unset($student[$key]['deviceToken']);
                    unset($student[$key]['device_type']);
                }
                $data['type']='Student';
                $data['report']=$request['report_type'];
                $data['data']=$student;
                session(['data' => $data]);
            }

            if ($request['report_type'] == 'Attendance By Year') {

                $this->validate($request, [
                    'school_id' => 'required',
                    'report_type' => 'required',
                    'yearfrom' => 'required',
                ]);


                $months = array('6' => 'June', '7' => 'July', '8' => 'August', '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December', '1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April', '5' => 'May');


                foreach ($student as $key => $value) {
                    $total_present_count = 0;
                    $total_working_days = 0;
                    $total_absent_count = 0;
                    $total_leave_count = 0;
                    $holiday_in_year = 0;
                    $year = $request['yearfrom'];
                    foreach ($months as $key1 => $value1) {
                        $month = $key1;
                        if ($month == '1') {
                            $year = $request['yearfrom'] + 1;
                        }
                        $start_date = "01-" . $month . "-" . $year;
                        $start_time = strtotime($start_date);
                        $end_time = strtotime("+1 month", $start_time);
                        for ($i = $start_time; $i < $end_time; $i += 86400) {
                            if (date('N', $i) <= 6 && !in_array(date('Y-m-d', $i), $holiday)) {
                                $total_working_days++;
                                $st_present = Attendance::where('attendance_date', date('Y-m-d', $i))->where('student_id', $value->id)->first();
                                if (!empty($st_present)) {
                                    if ($st_present->on_leave == 1) {
                                        $total_leave_count++;
                                    } else {
                                        $total_present_count++;
                                    }
                                } else {
                                    $total_absent_count++;
                                }
                            } else {
                                $holiday_in_year++;
                            }
                        }
                    }

                    $student[$key]['Present'] = $total_present_count;
                    $student[$key]['Absent'] = $total_absent_count;
                    $student[$key]['Leaves'] = $total_leave_count;
                    $student[$key]['Holidays'] = $holiday_in_year;
                    $student[$key]['Working Days'] = $total_working_days;

                    $school_name = School::select('name')->where('id', $value['school_id'])->first();
                    $student[$key]['School'] = $school_name->name;
                    $class_name = Class_Master::select('name')->where('id', $value['class_id'])->first();
                    $student[$key]['Class'] = $class_name->name;
                    unset($student[$key]['school_id']);
                    unset($student[$key]['class_id']);
                    unset($student[$key]['created_at']);
                    unset($student[$key]['updated_at']);
                    unset($student[$key]['deleted_at']);
                    unset($student[$key]['session_token']);
                    unset($student[$key]['remember_token']);
                    unset($student[$key]['location']);
                    unset($student[$key]['language']);
                    unset($student[$key]['staff_role']);
                    unset($student[$key]['role']);
                    unset($student[$key]['status']);
                    unset($student[$key]['notes']);
                    unset($student[$key]['password']);
                    unset($student[$key]['deviceToken']);
                    unset($student[$key]['device_type']);
                }


                $data['type']='Student';
                $data['report']=$request['report_type'];
                $data['data']=$student;
                session(['data' => $data]);
            }

            if ($request['report_type'] == 'Attendance By Device') {
                $this->validate($request, [
                    'device' => 'required',
                    'student_id'=>'required',
                    'from'=>'required',
                    'to'=>'required',
                ]);
                $data11=array();
                $student = User::where('role', 'student')->where('id', $request['student_id'])->first();
                if (empty($student)) {
                    Session::flash('message', 'No Record Found to Export!');
                    Session::flash('alert-class', 'alert-danger');
                    return redirect('report');
                }
                $Dates=array();
                $startTime = strtotime($request['from']);
                $endTime = strtotime($request['to']);
                for ($i = $startTime; $i <= $endTime; $i = $i + 86400) {
                    $Dates[] = date('Y-m-d', $i);
                }
                foreach ($Dates as $key => $date) {
                    $i = strtotime($date);
                    if (date('N', $i) <= 6 && !in_array(date('Y-m-d', $i), $holiday)) {
                        $check_present = AttendanceDetail::where('student_id', $request['student_id'])->where('device_id',$request['device'])->where('attendance_date', $date)->first();
                        if (!empty($check_present)) {
                            if ($check_present->on_leave == 1) {
                                $data11[$key]['Date'] = $date;
                                $data11[$key]['Attendance'] = 'Leave';
                                $data11[$key]['In Time'] = '-----';
                                $data11[$key]['Out Time'] = '----';
                                $data11[$key]['name'] = $student->name;

                            } else {
                                $data11[$key]['Date'] = $date;
                                $data11[$key]['Attendance'] = 'Present';
                                $data11[$key]['In Time'] = $check_present->school_in_time;
                                $data11[$key]['Out Time'] = $check_present->school_out_time;
                                $data11[$key]['name'] = $student->name;
                            }

                        } else {
                            $data11[$key]['Date'] = $date;
                            $data11[$key]['Attendance'] = 'Absent';
                            $data11[$key]['In Time'] = '';
                            $data11[$key]['Out Time'] = '';
                            $data11[$key]['name'] = $student->name;
                        }
                    } else {
                        $data11[$key]['Date'] = $date;
                        $data11[$key]['Attendance'] = 'Holiday';
                        $data11[$key]['In Time'] = '-----';
                        $data11[$key]['Out Time'] = '----';
                        $data11[$key]['name'] = $student->name;
                    }
                }

                $data['type']='Student';
                $data['report']=$request['report_type'];
                $data['data']=$data11;
                session(['data' => $data]);
            }

        }
        if($request['report_for']=='Staff'){

            $this->validate($request, [
                'report_type_staff' => 'required',
                'staff_school_id'=>'required',
            ]);

            $date = Carbon::today()->toDateString();

            if($request['staff_id'] != ''){
                $staff = User::where('role', 'staff')->where('id', $request['staff_id'])->get();
            }
            else if ($request['staff_school_id'] != '') {
                $staff = User::where('role', 'staff')->where('school_id', $request['staff_school_id'])->get();
            } else {
                $staff = new \Illuminate\Database\Eloquent\Collection;
            }

            if ($request['report_type_staff'] == 'Attendance Today' || $request['report_type_staff'] == 'Attendance By Date') {

                if ($request['report_type_staff'] == 'Attendance By Date') {
                    $this->validate($request, [
                        'date_staff'=>'required',
                    ]);
                    $date = $request['date_staff'];
                }
                $check_holiday=strtotime($date);

                if (date('N', $check_holiday) == 7 || in_array(date('Y-m-d',$check_holiday), $holiday)) {
                    Session::flash('message', 'There is Holiday on '.$date.', No records to export!');
                    Session::flash('alert-class', 'alert-danger');
                    Session::forget('data');
                    return redirect('report');
                }
                foreach ($staff as $key => $value) {
                    $check_present = Attendance::where('staff_id', $value->id)->where('attendance_date', $date)->first();
                    if (!empty($check_present)) {
                        if ($check_present->on_leave == 1) {
                            $staff[$key]['Attendance'] = 'Leave';
                            $staff[$key]['In Time'] = '';
                            $staff[$key]['Out Time'] = '';
                        } else {
                            $staff[$key]['Attendance'] = 'Present';
                            $staff[$key]['In Time'] = $check_present->school_in_time;
                            $staff[$key]['Out Time'] = $check_present->school_out_time;
                        }

                    } else {
                        $staff[$key]['Attendance'] = 'Absent';
                        $staff[$key]['In Time'] = '';
                        $staff[$key]['Out Time'] = '';
                    }
                    $school_name = School::select('name')->where('id', $value['school_id'])->first();
                    $staff[$key]['School'] = $school_name->name;

                    unset($staff[$key]['school_id']);
                    unset($staff[$key]['class_id']);
                    unset($staff[$key]['created_at']);
                    unset($staff[$key]['updated_at']);
                    unset($staff[$key]['deleted_at']);
                    unset($staff[$key]['session_token']);
                    unset($staff[$key]['remember_token']);
                    unset($staff[$key]['location']);
                    unset($staff[$key]['language']);
                    unset($staff[$key]['staff_role']);
                    unset($staff[$key]['role']);
                    unset($staff[$key]['status']);
                    unset($staff[$key]['notes']);
                    unset($staff[$key]['password']);
                    unset($staff[$key]['division']);
                    unset($staff[$key]['parents_name']);
                    unset($staff[$key]['deviceToken']);
                    unset($staff[$key]['device_type']);
                }

                $data['type']='Staff';
                $data['report']=$request['report_type_staff'];
                $data['data']=$staff;
                session(['data' => $data]);
            }

            if ($request['report_type_staff'] == 'Attendance By Month') {

                $this->validate($request, [
                    'month_staff' => 'required',
                ]);

                $year = Carbon::parse($request['month_staff'])->year;
                $month = Carbon::parse($request['month_staff'])->month;

                foreach ($staff as $key => $value){
                    $working_days = 0;
                    $present = 0;
                    $absent = 0;
                    $leaves = 0;
                    $holiday_in_month = 0;
                    $start_date = "01-" . $month . "-" . $year;
                    $start_time = strtotime($start_date);

                    $end_time = strtotime("+1 month", $start_time);

                    for ($i = $start_time; $i < $end_time; $i += 86400) {
                        if (date('N', $i) <= 6 && !in_array(date('Y-m-d', $i), $holiday)) {
                            $working_days++;
                            $st_present = Attendance::where('attendance_date', date('Y-m-d', $i))->where('staff_id', $value->id)->first();
                            if (!empty($st_present)) {
                                if ($st_present->on_leave == 1) {
                                    $leaves++;
                                } else {
                                    $present++;
                                }

                            } else {
                                $absent++;
                            }
                        } else {
                            $holiday_in_month++;
                        }
                    }

                    $staff[$key]['Present'] = $present;
                    $staff[$key]['Absent'] = $absent;
                    $staff[$key]['Leaves'] = $leaves;
                    $staff[$key]['Holidays'] = $holiday_in_month;
                    $staff[$key]['Working Days']=$working_days;

                    $school_name = School::select('name')->where('id', $value['school_id'])->first();
                    $staff[$key]['School'] = $school_name->name;
                    unset($staff[$key]['school_id']);
                    unset($staff[$key]['class_id']);
                    unset($staff[$key]['created_at']);
                    unset($staff[$key]['updated_at']);
                    unset($staff[$key]['deleted_at']);
                    unset($staff[$key]['session_token']);
                    unset($staff[$key]['remember_token']);
                    unset($staff[$key]['location']);
                    unset($staff[$key]['language']);
                    unset($staff[$key]['staff_role']);
                    unset($staff[$key]['role']);
                    unset($staff[$key]['status']);
                    unset($staff[$key]['notes']);
                    unset($staff[$key]['password']);
                    unset($staff[$key]['division']);
                    unset($staff[$key]['parents_name']);
                    unset($staff[$key]['deviceToken']);
                    unset($staff[$key]['device_type']);
                }

                $data['type']='Staff';
                $data['report']=$request['report_type_staff'];
                $data['data']=$staff;
                session(['data' => $data]);
            }

            if ($request['report_type_staff'] == 'Attendance By Year') {

                $this->validate($request, [
                    'yearfrom_staff' => 'required',
                ]);

                $months = array('6' => 'June', '7' => 'July', '8' => 'August', '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December', '1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April', '5' => 'May');

                foreach ($staff as $key => $value) {
                    $total_present_count = 0;
                    $total_working_days = 0;
                    $total_absent_count = 0;
                    $total_leave_count = 0;
                    $holiday_in_year = 0;
                    $year = $request['yearfrom_staff'];
                    foreach ($months as $key1 => $value1) {
                        $month = $key1;
                        if ($month == '1') {
                            $year = $request['yearfrom_staff'] + 1;
                        }
                        $start_date = "01-" . $month . "-" . $year;
                        $start_time = strtotime($start_date);
                        $end_time = strtotime("+1 month", $start_time);
                        for ($i = $start_time; $i < $end_time; $i += 86400) {
                            if (date('N', $i) <= 6 && !in_array(date('Y-m-d', $i), $holiday)) {
                                $total_working_days++;
                                $st_present = Attendance::where('attendance_date', date('Y-m-d', $i))->where('staff_id', $value->id)->first();
                                if (!empty($st_present)) {
                                    if ($st_present->on_leave == 1) {
                                        $total_leave_count++;
                                    } else {
                                        $total_present_count++;
                                    }
                                } else {
                                    $total_absent_count++;
                                }
                            } else {
                                $holiday_in_year++;
                            }
                        }
                    }

                    $staff[$key]['Present'] = $total_present_count;
                    $staff[$key]['Absent'] = $total_absent_count;
                    $staff[$key]['Leaves'] = $total_leave_count;
                    $staff[$key]['Holidays'] = $holiday_in_year;
                    $staff[$key]['Working Days'] = $total_working_days;

                    $school_name = School::select('name')->where('id', $value['school_id'])->first();
                    $staff[$key]['School'] = $school_name->name;

                    unset($staff[$key]['school_id']);
                    unset($staff[$key]['class_id']);
                    unset($staff[$key]['created_at']);
                    unset($staff[$key]['updated_at']);
                    unset($staff[$key]['deleted_at']);
                    unset($staff[$key]['session_token']);
                    unset($staff[$key]['remember_token']);
                    unset($staff[$key]['location']);
                    unset($staff[$key]['language']);
                    unset($staff[$key]['staff_role']);
                    unset($staff[$key]['role']);
                    unset($staff[$key]['status']);
                    unset($staff[$key]['notes']);
                    unset($staff[$key]['password']);
                    unset($staff[$key]['division']);
                    unset($staff[$key]['parents_name']);
                    unset($staff[$key]['deviceToken']);
                    unset($staff[$key]['device_type']);
                }

                $data['type']='Staff';
                $data['report']=$request['report_type_staff'];
                $data['data']=$staff;
                session(['data' => $data]);
            }

            if ($request['report_type_staff'] == 'Attendance By Device') {

                $this->validate($request, [
                    'device_staff' => 'required',
                    'staff_id'=>'required',
                    'staff_date_from'=>'required',
                    'staff_date_to'=>'required',
                ]);

                $staff = User::where('role', 'staff')->where('id', $request['staff_id'])->first();
                if (empty($staff)) {
                    Session::flash('message', 'No Record Found to Export!');
                    Session::flash('alert-class', 'alert-danger');
                    return redirect('report');
                }
                $Dates=array();
                $data=array();
                $startTime = strtotime($request['staff_date_from']);
                $endTime = strtotime($request['staff_date_to']);
                for ($i = $startTime; $i <= $endTime; $i = $i + 86400) {
                    $Dates[] = date('Y-m-d', $i);
                }
                foreach ($Dates as $key => $date) {
                    $i = strtotime($date);
                    if (date('N', $i) <= 6 && !in_array(date('Y-m-d', $i), $holiday)) {
                        $check_present = AttendanceDetail::where('staff_id', $request['staff_id'])->where('device_id',$request['device'])->where('attendance_date', $date)->first();
                        if (!empty($check_present)) {
                            if ($check_present->on_leave == 1) {
                                $data11[$key]['Date'] = $date;
                                $data11[$key]['Attendance'] = 'Leave';
                                $data11[$key]['In Time'] = '-----';
                                $data11[$key]['Out Time'] = '----';
                                $data11[$key]['name'] = $staff->name;

                            } else {
                                $data11[$key]['Date'] = $date;
                                $data11[$key]['Attendance'] = 'Present';
                                $data11[$key]['In Time'] = $check_present->school_in_time;
                                $data11[$key]['Out Time'] = $check_present->school_out_time;
                                $data11[$key]['name'] = $staff->name;
                            }

                        } else {
                            $data11[$key]['Date'] = $date;
                            $data11[$key]['Attendance'] = 'Absent';
                            $data11[$key]['In Time'] = '';
                            $data11[$key]['Out Time'] = '';
                            $data11[$key]['name'] = $staff->name;
                        }
                    } else {
                        $data11[$key]['Date'] = $date;
                        $data11[$key]['Attendance'] = 'Holiday';
                        $data11[$key]['In Time'] = '-----';
                        $data11[$key]['Out Time'] = '----';
                        $data11[$key]['name'] = $staff->name;

                    }
                }
                $data['type']='Staff';
                $data['report']=$request['report_type_staff'];
                $data['data']=$data11;
                session(['data' => $data]);
            }
        }
        return redirect('report');
    }
    public function export(Request $request){
        $data1= Session::get('data');
        Session::forget('data');
        if (count($data1['data']) == 0) {
            Session::flash('message', 'No Record Found to Export!');
            return redirect('report');
        }
       // return Session::get()->all();
       // Session::forget('data');

        //return redirect('report');

            Excel::create($data1['type'] . '-' . $data1['report'], function ($excel) use ($data1) {
                $excel->sheet($data1['report'], function ($sheet) use ($data1) {
                    $sheet->fromArray($data1['data']);
                });

            })->download('xlsx');
    }
    public function get_device($school_id)
    {
        $device = Device::where('school_id',$school_id)->get();
        echo "<option value=''>Please Select</option>";
        foreach ($device as $device1){
            echo '<option value="'.$device1['id'].'">'.$device1['device_type'].'</option>';
        }
        return "";
    }

    public function get_staff_teacher($school_id)
    {
        $teacher = User::where('school_id',$school_id)->where('role','staff')->where('staff_role','teacher')->get();
        echo "<option value=''>Please Select</option>";
        foreach ($teacher as $teacher1){
            echo '<option value="'.$teacher1['id'].'">'.$teacher1['name'].'</option>';
        }
        return "";
    }

}
