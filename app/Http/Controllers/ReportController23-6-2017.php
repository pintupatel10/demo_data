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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('staff');
        $this->middleware('principal');

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
       // self:: REPORT_PARENT => 'Attendance For Parents',
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

    public function index(Request $request)
    {
        $data['menu'] = "Report";
        $data['reports_student'] = self::$reports_student;
        $data['reports_staff'] = self::$reports_staff;
        $data['school_name'] = School::lists('name','id')->all();
        $data['device'] = Device::lists('device_type','id')->all();
        // $data['class']=Class_Master::lists('name','id')->all();

        if(!empty($request['submit'])) {
            $this->validate($request, [
                'report_for' => 'required',
            ]);

            $holiday = Calendar::where('school_id', $request['school_id'])->pluck('holiday_date')->toArray();

            if ($request['report_for'] == 'Student') {
                $this->validate($request, [
                    'report_type' => 'required',
                    'school_id' => 'required',
                    'class_name' => 'required',
                ]);
                $date = Carbon::today()->toDateString();
                if ($request['student_id'] != '') {
                    $student = User::where('role', 'student')->where('id', $request['student_id'])->paginate($this->pagination);
                } else if ($request['school_id'] != '' && $request['class_name'] != '' && $request['class_division'] != '') {
                    $student = User::where('role', 'student')->where('school_id', $request['school_id'])->where('class_id', $request['class_name'])->where('division', $request['class_division'])->paginate($this->pagination);

                } else if ($request['school_id'] != '' && $request['class_name'] != '') {
                    $student = User::where('role', 'student')->where('school_id', $request['school_id'])->where('class_id', $request['class_name'])->paginate($this->pagination);

                } else if ($request['school_id'] != '') {
                    $student = Student::where('role', 'student')->where('school_id', $request['school_id'])->paginate($this->pagination);
                } else {
                    $student = new \Illuminate\Database\Eloquent\Collection;
                }

                if ($request['report_type'] == 'Attendance Today' || $request['report_type'] == 'Attendance By Date') {
                    if ($request['report_type'] == 'Attendance By Date') {
                        $this->validate($request, [
                            'date' => 'required',
                        ]);
                        $date = $request['date'];
                        $date_facturation=Carbon::parse($request['date']);

                        if($date_facturation->isFuture()){
                            return back()->withInput()->withErrors(['date' => 'Date is in future please select other date !']);
                        }
                    }

                    $check_holiday = strtotime($date);

                    if (in_array(date('Y-m-d', $check_holiday), $holiday)) {
                        Session::flash('message', 'There is Holiday on ' . $date . ', No records to export!');
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

                    $data['type'] = 'Student';
                    $data['report'] = $request['report_type'];
                    $data['data'] = $student;
                   // session(['data' => $data]);
                }

                if ($request['report_type'] == 'Attendance For Parents') {

                    $this->validate($request, [
                        'school_id' => 'required',
                        'report_type' => 'required',
                        'student_id' => 'required',
                        'from' => 'required',
                        'to' => 'required',
                    ]);

//                    $date_facturation=Carbon::parse($request['from']);
//
//                    if($date_facturation->isFuture()){
//                        return back()->withInput()->withErrors(['from' => 'Date is in future please select other date !']);
//                    }

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
                        if (!in_array(date('Y-m-d', $i), $holiday)) {
                            $check_present = Attendance::where('student_id', $request['student_id'])->where('attendance_date', $date)->first();
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

                    $data['type'] = 'Student';
                    $data['report'] = $request['report_type'];
                    $data['data'] = $data11;
                    //session(['data' => $data]);
                }

                if ($request['report_type'] == 'Attendance By Month') {

                    $this->validate($request, [
                        'month' => 'required',
                    ]);

                    $date_facturation=Carbon::parse($request['month']);

                    if($date_facturation->isFuture()){
                        return back()->withInput()->withErrors(['month' => 'Month is in future please select other month !']);
                    }

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
                            if (!in_array(date('Y-m-d', $i), $holiday)) {
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
                    $data['type'] = 'Student';
                    $data['report'] = $request['report_type'];
                    $data['data'] = $student;
                   // session(['data' => $data]);
                }

                if ($request['report_type'] == 'Attendance By Year') {

                    $this->validate($request, [
                        'school_id' => 'required',
                        'report_type' => 'required',
                        'yearfrom' => 'required',
                    ]);

                    $date_facturation=Carbon::create($request['yearfrom']);

                    if($date_facturation->isFuture()){
                        return back()->withInput()->withErrors(['yearfrom' => 'Year is in future please select other Year !']);
                    }

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
                                if (!in_array(date('Y-m-d', $i), $holiday)) {
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

                    $data['type'] = 'Student';
                    $data['report'] = $request['report_type'];
                    $data['data'] = $student;
                }

                if ($request['report_type'] == 'Attendance By Device') {
                    $this->validate($request, [
                        'device' => 'required',
                        'student_id' => 'required',
                        'from' => 'required',
                        'to' => 'required',
                    ]);

                    $date_facturation=Carbon::parse($request['from']);

                    if($date_facturation->isFuture()){
                        return back()->withInput()->withErrors(['from' => 'Date is in future please select other Date !']);
                    }

                    $student = User::where('role', 'student')->where('id', $request['student_id'])->first();
                    if (empty($student)) {
                        Session::flash('message', 'No Record Found to Export!');
                        Session::flash('alert-class', 'alert-danger');
                        return redirect('report');
                    }

                    $from=Carbon::parse($request['from'])->format('Y-m-d');
                    $to=Carbon::parse($request['to'])->format('Y-m-d');
                   $check_present = AttendanceDetail::where('student_id', $request['student_id'])->where('device_id', $request['device'])->where('attendance_date','>=',$from)->where('attendance_date','<=',$to)->Paginate($this->pagination);

                    $data['type'] = 'Student';
                    $data['report'] = $request['report_type'];
                    $data['data'] = $check_present;
                    $data['name']=$student->name;
                }

            }
           // return $data['data'];
            if ($request['report_for'] == 'Staff') {

                $this->validate($request, [
                    'report_type_staff' => 'required',
                    'staff_school_id' => 'required',
                ]);

                $date = Carbon::today()->toDateString();

                if ($request['staff_id'] != '') {
                    $staff = User::where('role', 'staff')->where('id', $request['staff_id'])->paginate($this->pagination);
                } else if ($request['staff_school_id'] != '') {
                    $staff = User::where('role', 'staff')->where('school_id', $request['staff_school_id'])->paginate($this->pagination);
                } else {
                    $staff = new \Illuminate\Database\Eloquent\Collection;
                }

                if ($request['report_type_staff'] == 'Attendance Today' || $request['report_type_staff'] == 'Attendance By Date') {

                    if ($request['report_type_staff'] == 'Attendance By Date') {
                        $this->validate($request, [
                            'date_staff' => 'required',
                        ]);
                        $date = $request['date_staff'];
                        $date_facturation=Carbon::parse($request['date_staff']);

                        if($date_facturation->isFuture()){
                            return back()->withInput()->withErrors(['date_staff' => 'Date is in future please select other date !']);
                        }
                    }

                    $check_holiday = strtotime($date);

                    if (in_array(date('Y-m-d', $check_holiday), $holiday)) {
                        Session::flash('message', 'There is Holiday on ' . $date . ', No records to export!');
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

                    $data['type'] = 'Staff';
                    $data['report'] = $request['report_type_staff'];
                    $data['data'] = $staff;
                   // session(['data' => $data]);
                }

                if ($request['report_type_staff'] == 'Attendance By Month') {

                    $this->validate($request, [
                        'month_staff' => 'required',
                    ]);

                    $date_facturation=Carbon::parse($request['month_staff']);

                    if($date_facturation->isFuture()){
                        return back()->withInput()->withErrors(['month_staff' => 'Month is in future please select other month !']);
                    }

                    $year = Carbon::parse($request['month_staff'])->year;
                    $month = Carbon::parse($request['month_staff'])->month;

                    foreach ($staff as $key => $value) {
                        $working_days = 0;
                        $present = 0;
                        $absent = 0;
                        $leaves = 0;
                        $holiday_in_month = 0;
                        $start_date = "01-" . $month . "-" . $year;
                        $start_time = strtotime($start_date);

                        $end_time = strtotime("+1 month", $start_time);

                        for ($i = $start_time; $i < $end_time; $i += 86400) {
                            if (!in_array(date('Y-m-d', $i), $holiday)) {
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
                        $staff[$key]['Working Days'] = $working_days;

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

                    $data['type'] = 'Staff';
                    $data['report'] = $request['report_type_staff'];
                    $data['data'] = $staff;
                  //  session(['data' => $data]);
                }

                if ($request['report_type_staff'] == 'Attendance By Year') {

                    $this->validate($request, [
                        'yearfrom_staff' => 'required',
                    ]);

                    $date_facturation=Carbon::create($request['yearfrom_staff']);

                    if($date_facturation->isFuture()){
                        return back()->withInput()->withErrors(['yearfrom_staff' => 'Year is in future please select other Year !']);
                    }

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
                                if (!in_array(date('Y-m-d', $i), $holiday)) {
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

                    $data['type'] = 'Staff';
                    $data['report'] = $request['report_type_staff'];
                    $data['data'] = $staff;
                   // session(['data' => $data]);
                }

                if ($request['report_type_staff'] == 'Attendance By Device') {

                    $this->validate($request, [
                        'device_staff' => 'required',
                        'staff_id' => 'required',
                        'staff_date_from' => 'required',
                        'staff_date_to' => 'required',
                    ]);

                    $date_facturation=Carbon::parse($request['staff_date_from']);

                    if($date_facturation->isFuture()){
                        return back()->withInput()->withErrors(['staff_date_from' => 'Date is in future please select other Date !']);
                    }

                    $staff = User::where('role', 'staff')->where('id', $request['staff_id'])->first();
                    if (empty($staff)) {
                        Session::flash('message', 'No Record Found to Export!');
                        Session::flash('alert-class', 'alert-danger');
                        return redirect('report');
                    }

                    $from=Carbon::parse($request['staff_date_from'])->format('Y-m-d');
                    $to=Carbon::parse($request['staff_date_to'])->format('Y-m-d');
                    $check_present = AttendanceDetail::where('staff_id', $request['staff_id'])->where('device_id', $request['device_staff'])->where('attendance_date','>=',$from)->where('attendance_date','<=',$to)->Paginate($this->pagination);
                    $data['type'] = 'Staff';
                    $data['report'] =$request['report_type_staff'];
                    $data['data'] = $check_present;
                    $data['name']=$staff->name;
                }
            }
        }
        return view('report.index', $data);
    }

    public function export(Request $request)
    {
        if(!empty($request['export'])) {
            $this->validate($request, [
                'report_for' => 'required',
            ]);

            $holiday = Calendar::where('school_id', $request['school_id'])->pluck('holiday_date')->toArray();

            if ($request['report_for'] == 'Student') {
                $this->validate($request, [
                    'report_type' => 'required',
                    'school_id' => 'required',
                    'class_name' => 'required',
                ]);
                $date = Carbon::today()->toDateString();
                if ($request['student_id'] != '') {
                    $student = User::where('role', 'student')->where('id', $request['student_id'])->get();
                } else if ($request['school_id'] != '' && $request['class_name'] != '' && $request['class_division'] != '') {
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
                        $date_facturation=Carbon::parse($request['date']);

                        if($date_facturation->isFuture()){
                            return back()->withInput()->withErrors(['date' => 'Date is in future please select other date !']);
                        }
                    }

                    $check_holiday = strtotime($date);

                    if (in_array(date('Y-m-d', $check_holiday), $holiday)) {
                        Session::flash('message', 'There is Holiday on ' . $date . ', No records to export!');
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

                    $data['type'] = 'Student';
                    $data['report'] = $request['report_type'];
                    $data['data'] = $student;

                    Excel::create($data['type'] . '-' . $data['report'], function ($excel) use ($data) {
                        $excel->sheet($data['report'], function ($sheet) use ($data) {
                            $sheet->fromArray($data['data']);
                        });

                    })->download($request['export_type']);
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
                        if (!in_array(date('Y-m-d', $i), $holiday)) {
                            $check_present = Attendance::where('student_id', $request['student_id'])->where('attendance_date', $date)->first();
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

                    $data['type'] = 'Student';
                    $data['report'] = $request['report_type'];
                    $data['data'] = $data11;

                    Excel::create($data['type'] . '-' . $data['report'], function ($excel) use ($data) {
                        $excel->sheet($data['report'], function ($sheet) use ($data) {
                            $sheet->fromArray($data['data']);
                        });

                    })->download($request['export_type']);
                }

                if ($request['report_type'] == 'Attendance By Month') {

                    $this->validate($request, [
                        'month' => 'required',
                    ]);

                    $date_facturation=Carbon::parse($request['month']);

                    if($date_facturation->isFuture()){
                        return back()->withInput()->withErrors(['month' => 'Month is in future please select other month !']);
                    }

                    $Displaymonth = Carbon::parse($request['month'])->format('F Y');

                    $year = Carbon::parse($request['month'])->year;
                    $month = Carbon::parse($request['month'])->month;
                     $stdata['title']='('.$month.' '.$year.')';
                    foreach ($student as $key => $value) {
                        $sdata[$key]['Studentname']=$value['name'];
                        $sdata[$key]['Rollno']=$value['roll_no'];


                       $start_date = "01-" . $month . "-" . $year;
                        $start_time = strtotime($start_date);

                       $end_time = strtotime("+1 month", $start_time);
                        $days=0;
                        for ($i = $start_time; $i < $end_time; $i += 86400) {
                            if (!in_array(date('Y-m-d', $i), $holiday)) {

                                $st_present = Attendance::where('attendance_date', date('Y-m-d', $i))->where('student_id', $value->id)->first();
                                if (!empty($st_present)) {
                                    if ($st_present->on_leave == 1) {
                                        $sdata[$key]['attendance'][$days]['attendance']='L';
                                    } else {
                                        $sdata[$key]['attendance'][$days]['attendance']='P';
                                    }

                                } else {
                                    $sdata[$key]['attendance'][$days]['attendance']='A';
                                }
                            } else {
                                $sdata[$key]['attendance'][$days]['attendance']='H';

                            }
                            $days++;
                        }
                       $class_name = Class_Master::select('name')->where('id', $value['class_id'])->first();
                    }

                    $data['type'] = 'Student';
                    $data['report'] = $request['report_type'];
                    $data['data'] = $student;
                    $ssdata=array_values($sdata);


                    Excel::create($data['type'] . '-' . $data['report'], function ($excel) use ($data,$ssdata,$days,$class_name,$Displaymonth) {
                        $excel->sheet($data['report'], function ($sheet) use ($ssdata,$days,$class_name,$Displaymonth) {
                              $i = 0;
                              $data100[$i - 1] = [$class_name->name . '(' . $Displaymonth . ')'];
                              if ($days == 31) {
                                  $data100[$i] = ['Student Name', 'Roll No', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31'];
                              }
                              if ($days == 30) {
                                  $data100[$i] = ['Student Name', 'Roll No', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30'];
                              }
                              if ($days == 29) {
                                  $data100[$i] = ['Student Name', 'Roll No', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29'];
                              }
                              if ($days == 28) {
                                  $data100[$i] = ['Student Name', 'Roll No', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28'];
                              }
                              foreach ($ssdata as $key => $val) {
                                  $i++;
                                  $data100[$i] = [
                                      $val['Studentname'],
                                      $val['Rollno'],
                                  ];
                                  foreach ($val['attendance'] as $k => $v) {
                                      array_push($data100[$i], $v['attendance']);
                                  }
                              }
                              $sheet->fromArray($data100);

                        });


                    })->download($request['export_type']);
                }

                if ($request['report_type'] == 'Attendance By Year') {

                    $this->validate($request, [
                        'school_id' => 'required',
                        'report_type' => 'required',
                        'yearfrom' => 'required',
                    ]);

                    $date_facturation=Carbon::create($request['yearfrom']);

                    if($date_facturation->isFuture()){
                        return back()->withInput()->withErrors(['yearfrom' => 'Year is in future please select other Year !']);
                    }

                    $months = array('6' => 'June', '7' => 'July', '8' => 'August', '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December', '1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April', '5' => 'May');
                    $year = $request['yearfrom'];
                    $class_name = Class_Master::select('name')->where('id',$request['class_name'])->first();

                 //   $Displaymonth = Carbon::parse($request['month'])->format('F Y');
                    foreach ($months as $keym =>$valuem) {
                        $month = $keym;
                      if ($month == '1') {
                          $year = $request['yearfrom'] + 1;
                        }
                        $ssdisplay[$keym]['Displaymonth']=$valuem.'-'.$year;
                        foreach ($student as $key => $value) {
                            $sdata[$keym][$key]['Studentname'] = $value['name'];
                            $sdata[$keym][$key]['Rollno'] = $value['roll_no'];

                            $start_date = "01-" . $month . "-" . $year;
                            $start_time = strtotime($start_date);

                            $end_time = strtotime("+1 month", $start_time);
                            $days = 0;
                            for ($i = $start_time; $i < $end_time; $i += 86400) {
                                if (!in_array(date('Y-m-d', $i), $holiday)) {

                                    $st_present = Attendance::where('attendance_date', date('Y-m-d', $i))->where('student_id', $value->id)->first();
                                    if (!empty($st_present)) {
                                        if ($st_present->on_leave == 1) {
                                            $sdata[$keym][$key]['attendance'][$days]['attendance'] = 'L';
                                        } else {
                                            $sdata[$keym][$key]['attendance'][$days]['attendance'] = 'P';
                                        }

                                    } else {
                                        $sdata[$keym][$key]['attendance'][$days]['attendance'] = 'A';
                                    }
                                } else {
                                    $sdata[$keym][$key]['attendance'][$days]['attendance'] = 'H';
                                }
                                $days++;
                            }
                            $class_name = Class_Master::select('name')->where('id', $value['class_id'])->first();
                        }
                        $ssdisplay[$keym]['days']=$days;
                    }
                    $data['type'] = 'Student';
                    $data['report'] = $request['report_type'];
                    $data['data'] = $student;

                    Excel::create($data['type'] . '-' . $data['report'].' '.$request['yearfrom'].'-'.$year, function ($excel) use ($data,$sdata,$ssdisplay,$class_name) {
                        $excel->sheet($data['report'], function ($sheet) use ($sdata,$class_name, $ssdisplay) {
                            $i = 0;
                           foreach ($sdata as $kk => $value00){
                               $i=$i+1;
                               $data100[$i]=[''];
                               $i=$i+1;
                             $data100[$i] = [$class_name->name. '  (' . $ssdisplay[$kk]['Displaymonth'] . ')'];
                               $i=$i+1;
                            if ($ssdisplay[$kk]['days'] == 31) {
                                $data100[$i] = ['Student Name', 'Roll No', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31'];
                            }
                            if ($ssdisplay[$kk]['days'] == 30) {
                                $data100[$i] = ['Student Name', 'Roll No', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30'];
                            }
                            if ($ssdisplay[$kk]['days'] == 29) {
                                $data100[$i] = ['Student Name', 'Roll No', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29'];
                            }
                            if ($ssdisplay[$kk]['days'] == 28) {
                                $data100[$i] = ['Student Name', 'Roll No', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28'];
                            }
                               $i=$i+1;
                            foreach ($sdata[$kk] as $key => $val) {

                                $data100[$i] = [
                                    $val['Studentname'],
                                    $val['Rollno'],
                                ];
                                foreach ($val['attendance'] as $k => $v) {
                                    array_push($data100[$i], $v['attendance']);
                                 }
                                $i++;
                                }
                             }
                            $sheet->fromArray($data100);
                        });

                    })->download($request['export_type']);
                }

                if ($request['report_type'] == 'Attendance By Device') {
                    $this->validate($request, [
                        'device' => 'required',
                        'student_id' => 'required',
                        'from' => 'required',
                        'to' => 'required',
                    ]);

                    $date_facturation=Carbon::parse($request['from']);

                    if($date_facturation->isFuture()){
                        return back()->withInput()->withErrors(['from' => 'Date is in future please select other Date !']);
                    }

                    $student = User::where('role', 'student')->where('id', $request['student_id'])->first();
                    if (empty($student)) {
                        Session::flash('message', 'No Record Found to Export!');
                        Session::flash('alert-class', 'alert-danger');
                        return redirect('report');
                    }

                    $from=Carbon::parse($request['from'])->format('Y-m-d');
                    $to=Carbon::parse($request['to'])->format('Y-m-d');
                    $check_present = AttendanceDetail::select('rfid_no','school_id','student_id','class_name','class_division','school_in_time','school_out_time','attendance_date','attendance_time','device_id','latittude','longitude')->where('student_id', $request['student_id'])->where('device_id', $request['device'])->where('attendance_date','>=',$from)->where('attendance_date','<=',$to)->get();
                    $data['type'] = 'Student';
                    $data['report'] = $request['report_type'];
                    $data['data'] = $check_present;
                    $data['name']=$student->name;

                    Excel::create($data['type'] . '-' . $data['report'], function ($excel) use ($data) {
                        $excel->sheet($data['report'], function ($sheet) use ($data) {
                            $sheet->fromArray($data['data']);
                        });

                    })->download($request['export_type']);
                }
            }
            if ($request['report_for'] == 'Staff') {

                $this->validate($request, [
                    'report_type_staff' => 'required',
                    'staff_school_id' => 'required',
                ]);

                $date = Carbon::today()->toDateString();

                if ($request['staff_id'] != '') {
                    $staff = User::where('role', 'staff')->where('id', $request['staff_id'])->get();
                } else if ($request['staff_school_id'] != '') {
                    $staff = User::where('role', 'staff')->where('school_id', $request['staff_school_id'])->get();
                } else {
                    $staff = new \Illuminate\Database\Eloquent\Collection;
                }

                if ($request['report_type_staff'] == 'Attendance Today' || $request['report_type_staff'] == 'Attendance By Date') {

                    if ($request['report_type_staff'] == 'Attendance By Date') {
                        $this->validate($request, [
                            'date_staff' => 'required',
                        ]);
                        $date = $request['date_staff'];
                        $date_facturation=Carbon::parse($request['date_staff']);

                        if($date_facturation->isFuture()){
                            return back()->withInput()->withErrors(['date_staff' => 'Date is in future please select other date !']);
                        }
                    }
                    $check_holiday = strtotime($date);

                    if (in_array(date('Y-m-d', $check_holiday), $holiday)) {
                        Session::flash('message', 'There is Holiday on ' . $date . ', No records to export!');
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

                    $data['type'] = 'Staff';
                    $data['report'] = $request['report_type_staff'];
                    $data['data'] = $staff;

                    Excel::create($data['type'] . '-' . $data['report'], function ($excel) use ($data) {
                        $excel->sheet($data['report'], function ($sheet) use ($data) {
                            $sheet->fromArray($data['data']);
                        });

                    })->download($request['export_type']);
                }

                if ($request['report_type_staff'] == 'Attendance By Month') {

                    $this->validate($request, [
                        'month_staff' => 'required',
                    ]);

                    $date_facturation=Carbon::parse($request['month_staff']);

                    if($date_facturation->isFuture()){
                        return back()->withInput()->withErrors(['month_staff' => 'Month is in future please select other month !']);
                    }

                    $Displaymonth = Carbon::parse($request['month'])->format('F Y');

                    $year = Carbon::parse($request['month'])->year;
                    $month = Carbon::parse($request['month'])->month;
                    $stdata['title']='('.$month.' '.$year.')';
                    foreach ($staff as $key => $value) {
                        $sdata[$key]['StaffName']=$value['name'];
                        $sdata[$key]['Role']=$value['staff_role'];


                        $start_date = "01-" . $month . "-" . $year;
                        $start_time = strtotime($start_date);

                        $end_time = strtotime("+1 month", $start_time);
                        $days=0;
                        for ($i = $start_time; $i < $end_time; $i += 86400) {
                            if (!in_array(date('Y-m-d', $i), $holiday)) {

                                $st_present = Attendance::where('attendance_date', date('Y-m-d', $i))->where('staff_id', $value->id)->first();
                                if (!empty($st_present)) {
                                    if ($st_present->on_leave == 1) {
                                        $sdata[$key]['attendance'][$days]['attendance']='L';
                                    } else {
                                        $sdata[$key]['attendance'][$days]['attendance']='P';
                                    }

                                } else {
                                    $sdata[$key]['attendance'][$days]['attendance']='A';
                                }
                            } else {
                                $sdata[$key]['attendance'][$days]['attendance']='H';

                            }
                            $days++;
                        }
                        $school_name = School::select('name')->where('id', $value['school_id'])->first();
                    }

                    $data['type'] = 'Staff';
                    $data['report'] = $request['report_type_staff'];
                    $data['data'] = $staff;
                    $ssdata=array_values($sdata);

                    Excel::create($data['type'] . '-' . $data['report'].' '.$Displaymonth, function ($excel) use ($data,$ssdata,$days,$school_name,$Displaymonth) {
                        $excel->sheet($data['report'], function ($sheet) use ($ssdata,$days,$school_name,$Displaymonth) {

                            $i=0;
                            $data100[$i-1] = [$school_name->name.'  ('.$Displaymonth.')'];
                            if($days == 31) {
                                $data100[$i] = ['Staff Name', 'Role', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31'];
                            }
                            if($days == 30) {
                                $data100[$i] = ['Staff Name', 'Role', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30'];
                            }
                            if($days == 29) {
                                $data100[$i] = ['Staff Name', 'Role', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29'];
                            }
                            if($days == 28) {
                                $data100[$i] = ['Staff Name', 'Role', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28'];
                            }
                            foreach ($ssdata as $key => $val) {
                                $i++;
                                $data100[$i] = [
                                    $val['StaffName'],
                                    $val['Role'],
                                ];
                                foreach($val['attendance'] as $k=>$v){
                                    array_push($data100[$i], $v['attendance']);
                                }
                            }


                            $sheet->fromArray($data100);
                        });

                    })->download($request['export_type']);

                }

                if ($request['report_type_staff'] == 'Attendance By Year') {

                    $this->validate($request, [
                        'yearfrom_staff' => 'required',
                    ]);

                    $date_facturation=Carbon::create($request['yearfrom_staff']);

                    if($date_facturation->isFuture()){
                        return back()->withInput()->withErrors(['yearfrom_staff' => 'Year is in future please select other Year !']);
                    }

                    $months = array('6' => 'June', '7' => 'July', '8' => 'August', '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December', '1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April', '5' => 'May');


                    $year = $request['yearfrom_staff'];

                    //   $Displaymonth = Carbon::parse($request['month'])->format('F Y');
                    foreach ($months as $keym =>$valuem) {
                        $month = $keym;
                        if ($month == '1') {
                            $year = $request['yearfrom'] + 1;
                        }
                        $ssdisplay[$keym]['Displaymonth']=$valuem.'-'.$year;
                        foreach ($staff as $key => $value) {

                            $sdata[$keym][$key]['StaffName'] = $value['name'];
                            $sdata[$keym][$key]['Role'] = $value['staff_role'];

                            $start_date = "01-" . $month . "-" . $year;
                            $start_time = strtotime($start_date);

                            $end_time = strtotime("+1 month", $start_time);
                            $days = 0;
                            for ($i = $start_time; $i < $end_time; $i += 86400) {
//
//                                $check_future_date=Carbon::parse(date('Y-m-d', $i));
//
//                                if($check_future_date->isFuture()){
//                                    break;
//                                }

                                if (!in_array(date('Y-m-d', $i), $holiday)) {

                                    $st_present = Attendance::where('attendance_date', date('Y-m-d', $i))->where('staff_id', $value->id)->first();
                                    if (!empty($st_present)) {
                                        if ($st_present->on_leave == 1) {
                                            $sdata[$keym][$key]['attendance'][$days]['attendance'] = 'L';
                                        } else {
                                            $sdata[$keym][$key]['attendance'][$days]['attendance'] = 'P';
                                        }

                                    } else {
                                        $sdata[$keym][$key]['attendance'][$days]['attendance'] = 'A';
                                    }
                                } else {
                                    $sdata[$keym][$key]['attendance'][$days]['attendance'] = 'H';
                                }
                                $days++;
                            }
                           // $class_name = Class_Master::select('name')->where('id', $value['class_id'])->first();
                        }
                        $ssdisplay[$keym]['days']=$days;
                    }

                    $data['type'] = 'Staff';
                    $data['report'] =$request['report_type_staff'];
                    $data['data'] = $staff;

                    Excel::create($data['type'] . '-' . $data['report'].' '.$request['yearfrom_staff'].'-'.$year, function ($excel) use ($data,$sdata,$ssdisplay) {
                        $excel->sheet($data['report'], function ($sheet) use ($sdata, $ssdisplay) {
                            $i = 0;
                            foreach ($sdata as $kk => $value00){
                                $i=$i+1;
                                $data100[$i]=[''];
                                $i=$i+1;
                                $data100[$i] = [' (' . $ssdisplay[$kk]['Displaymonth'] . ')'];
                                $i=$i+1;
                                if ($ssdisplay[$kk]['days'] == 31) {
                                    $data100[$i] = ['Staff Name', 'Role', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31'];
                                }
                                if ($ssdisplay[$kk]['days'] == 30) {
                                    $data100[$i] = ['Staff Name', 'Role', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30'];
                                }
                                if ($ssdisplay[$kk]['days'] == 29) {
                                    $data100[$i] = ['Staff Name', 'Role', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29'];
                                }
                                if ($ssdisplay[$kk]['days'] == 28) {
                                    $data100[$i] = ['Staff Name', 'Role', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28'];
                                }
                                $i=$i+1;
                                foreach ($sdata[$kk] as $key => $val) {

                                    $data100[$i] = [
                                        $val['StaffName'],
                                        $val['Role'],
                                    ];
                                    foreach ($val['attendance'] as $k => $v) {
                                        array_push($data100[$i], $v['attendance']);
                                    }
                                    $i++;
                                }
                            }
                            $sheet->fromArray($data100);
                        });

                    })->download($request['export_type']);
                }

                if ($request['report_type_staff'] == 'Attendance By Device') {
                    $this->validate($request, [
                        'device_staff' => 'required',
                        'staff_id' => 'required',
                        'staff_date_from' => 'required',
                        'staff_date_to' => 'required',
                    ]);

                    $date_facturation=Carbon::parse($request['staff_date_from']);

                    if($date_facturation->isFuture()){
                        return back()->withInput()->withErrors(['staff_date_from' => 'Date is in future please select other Date !']);
                    }

                    $staff = User::where('role', 'staff')->where('id', $request['staff_id'])->first();
                    if (empty($staff)) {
                        Session::flash('message', 'No Record Found to Export!');
                        Session::flash('alert-class', 'alert-danger');
                        return redirect('report');
                    }
                    $from=Carbon::parse($request['staff_date_from'])->format('Y-m-d');
                    $to=Carbon::parse($request['staff_date_to'])->format('Y-m-d');

                    $check_present = AttendanceDetail::select('rfid_no','school_id','staff_id','staff_name','staff_role','school_in_time','school_out_time','attendance_date','attendance_time','device_id','latittude','longitude')
                        ->where('staff_id', $request['staff_id'])->where('device_id', $request['device_staff'])
                            ->where('attendance_date','>=',$from)->where('attendance_date','<=',$to)->get();
                    $data['type'] = 'Staff';
                    $data['report'] =$request['report_type_staff'];
                    $data['data'] = $check_present;
                    $data['name']=$staff->name;

                    Excel::create($data['type'] . '-' . $data['report'], function ($excel) use ($data) {
                        $excel->sheet($data['report'], function ($sheet) use ($data) {
                            $sheet->fromArray($data['data']);
                        });

                    })->download($request['export_type']);
                }
            }
        }
        return redirect('report');
    }
//    public function export(Request $request){
//        $data1= Session::get('data');
//        Session::forget('data');
//        if (count($data1['data']) == 0) {
//            Session::flash('message', 'No Record Found to Export!');
//            return redirect('report');
//        }
//       // return Session::get()->all();
//       // Session::forget('data');
//
//        //return redirect('report');
//
//            Excel::create($data1['type'] . '-' . $data1['report'], function ($excel) use ($data1) {
//                $excel->sheet($data1['report'], function ($sheet) use ($data1) {
//                    $sheet->fromArray($data1['data']);
//                });
//
//            })->download($request['export_type']);
//    }

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
