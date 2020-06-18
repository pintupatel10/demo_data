<?php

namespace App\Http\Controllers;

use App\Attendance;
use App\Calendar;
use App\Class_Master;
use App\Division;
use App\School;
use App\Student;
use App\User;
use Carbon\Carbon;
use Illuminate\Contracts\Redis\Database;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('staff');
    }
   // const REPORT_0='';
    const REPORT_TODAY = 'Attendance Today';
  //  const REPORT_CLASS = 'Attendance By Class';
    const REPORT_DATE = 'Attendance By Date';
    const REPORT_PARENT = 'Attendance For Parents';
    const REPORT_MONTH = 'Attendance By Month';
    const REPORT_YEAR = 'Attendance By Year';



    public static $reports = [
       // self:: REPORT_0 =>'Please Select',
        self:: REPORT_TODAY => 'Attendance Today',
       // self:: REPORT_CLASS => 'Attendance By Class',
        self:: REPORT_DATE => 'Attendance By Date',
        self:: REPORT_PARENT => 'Attendance For Parents',
        self:: REPORT_MONTH => 'Attendance By Month',
        self:: REPORT_YEAR => 'Attendance By Year',
    ];

    public function index()
    {
        $data['menu'] = "Report";
        $data['reports'] = self::$reports;
        $data['school_name'] = School::lists('name','id')->all();

        return view('report.index', $data);
    }

    public function export(Request $request)
    {
        
        $this->validate($request, [
            'school_id' => 'required',
            'report_type' => 'required',
        ]);
        $input = $request->all();

        $date = Carbon::today()->toDateString();

        if ($request['report_type'] == 'Attendance Today' || $request['report_type'] == 'Attendance By Date') {
            if ($request['report_type'] == 'Attendance By Date') {

                $date =$request['date'];
            }

            if ($request['school_id'] != '' && $request['class_name'] != '' && $request['class_division'] != '') {
                $student = User::where('role', 'student')->where('school_id', $request['school_id'])->where('class_id', $request['class_name'])->where('division', $request['class_division'])->orderBy('class_id', 'ASC')->get();

            }
            else if ($request['school_id'] != '' && $request['class_name'] != '') {
                $student = User::where('role', 'student')->where('school_id', $request['school_id'])->where('class_id', $request['class_name'])->orderBy('class_id','ASC')->get();

            }
            else if($request['school_id'] != '') {

                $student = Student::where('role', 'student')->where('school_id', $request['school_id'])->orderBy('class_id', 'ASC')->get();
            }
            else{
                $student= new \Illuminate\Database\Eloquent\Collection;
            }

            if(count($student) == 0){
                Session::flash('message', 'No Record Found to Export!');
                Session::flash('alert-class', 'alert-danger');
                return redirect('report');
            }
                foreach ($student as $key => $value) {
                    $check_present = Attendance::where('student_id', $value->id)->where('attendance_date', $date)->first();
                    if (!empty($check_present)) {
                        if($check_present->on_leave == 1){
                            $student[$key]['Attendance'] = 'Leave';
                            $student[$key]['In Time'] = '';
                            $student[$key]['Out Time'] = '';
                        }
                        else {
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
                }

                  Excel::create('Student '.$request['report_type'], function ($excel) use ($student,$date) {

                    $excel->sheet('Attendance '.$date, function ($sheet) use ($student) {
                        $sheet->fromArray($student);
                    });

                })->download('xlsx');

        }

        if ($request['report_type'] == 'Attendance For Parents') {

            $this->validate($request, [
                'school_id' => 'required',
                'report_type' => 'required',
                'student_id'=>'required',
                'from'=>'required',
                'to'=>'required',
            ]);

             $student = User::where('role','student')->where('id',$request['student_id'])->first();
            if(empty($student)){
                Session::flash('message', 'No Record Found to Export!');
                Session::flash('alert-class', 'alert-danger');
                return redirect('report');
            }
            $holiday=Calendar::get()->toArray();
            $startTime = strtotime($request['from']);
            $endTime = strtotime($request['to']);
            for ( $i = $startTime; $i <= $endTime; $i = $i + 86400 ) {
                $Dates[] = date( 'Y-m-d',$i);
            }
            foreach($Dates as $key=> $date){
                $i=strtotime($date);
                if(date('N',$i) <= 6 && !in_array(date('Y-m-d', $i),$holiday)) {
                    $check_present = Attendance::where('student_id',$request['student_id'])->where('attendance_date',$date)->first();
                    if (!empty($check_present)){
                        if($check_present->on_leave == 1) {
                            $data[$key]['Date'] = $date;
                            $data[$key]['Attendance'] = 'Leave';
                            $data[$key]['In Time'] = '-----';
                            $data[$key]['Out Time'] = '----';
                        }
                        else {
                            $data[$key]['Date'] = $date;
                            $data[$key]['Attendance'] = 'Present';
                            $data[$key]['In Time'] = $check_present->school_in_time;
                            $data[$key]['Out Time'] = $check_present->school_out_time;
                        }

                } else {
                    $data[$key]['Date'] = $date;
                    $data[$key]['Attendance'] = 'Absent';
                    $data[$key]['In Time'] = '';
                    $data[$key]['Out Time'] = '';
                }
                }
                else{
                    $data[$key]['Date'] = $date;
                    $data[$key]['Attendance'] = 'Holiday';
                    $data[$key]['In Time'] = '-----';
                    $data[$key]['Out Time'] = '----';
                }
            }
            unset($student['school_id']);
            unset($student['class_id']);
            unset($student['created_at']);
            unset($student['updated_at']);
            unset($student['deleted_at']);
            unset($student['session_token']);
            unset($student['remember_token']);
            unset($student['location']);
            unset($student['language']);
            unset($student['staff_role']);
            unset($student['role']);
            unset($student['status']);
            unset($student['notes']);
            unset($student['password']);

            Excel::create('Student '.$request['report_type'], function ($excel) use ($data) {

                $excel->sheet('Attendance', function ($sheet) use ($data) {
                    $sheet->fromArray($data);
                });

            })->download('xlsx');
        }

        if ($request['report_type'] == 'Attendance By Month') {

            $this->validate($request, [
                'school_id' => 'required',
                'report_type' => 'required',
                'month'=>'required',
            ]);

                if ($request['school_id'] != '' && $request['class_name'] != '' && $request['class_division'] != '') {
                    $student = User::where('role', 'student')->where('school_id', $request['school_id'])->where('class_id', $request['class_name'])->where('division', $request['class_division'])->get();

                }
                else if ($request['school_id'] != '' && $request['class_name'] != '') {
                    $student = User::where('role', 'student')->where('school_id', $request['school_id'])->where('class_id', $request['class_name'])->get();

                }
                else if($request['school_id'] != '') {

                    $student = Student::where('role', 'student')->where('school_id', $request['school_id'])->get();
                }
                else{
                    $student= new \Illuminate\Database\Eloquent\Collection;
                }

                if(count($student) == 0){
                    Session::flash('message', 'No Record Found to Export!');
                    Session::flash('alert-class', 'alert-danger');
                    return redirect('report');
                }

            $year = Carbon::parse($request['month'])->year;
            $month = Carbon::parse($request['month'])->month;
            $holiday=Calendar::get()->toArray();

            foreach ($student as $key => $value) {
                $working_days=0;
                $present=0;
                $absent=0;
                $leaves=0;
                $holiday_in_month=0;
                $start_date = "01-".$month."-".$year;
                $start_time = strtotime($start_date);

                $end_time = strtotime("+1 month", $start_time);

                for($i=$start_time; $i<$end_time; $i+=86400)
                {
                    if(date('N',$i) <= 6 && !in_array(date('Y-m-d', $i),$holiday)) {
                        $working_days++;
                        $st_present = Attendance::where('attendance_date', date('Y-m-d', $i))->where('student_id', $value->id)->first();
                        if (!empty($st_present)) {
                            if($st_present->on_leave == 1){
                                $leaves++;
                            }
                            else {
                                $present++;
                            }

                        } else {
                            $absent++;
                         }
                    }
                    else{
                        $holiday_in_month++;
                    }
                }

                $student[$key]['Present']=$present;
                $student[$key]['Absent']=$absent;
                $student[$key]['Leaves']=$leaves;
                $student[$key]['Holidays']=$holiday_in_month;

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
            }

                Excel::create('Student '.$request['report_type'], function ($excel) use ($student,$date) {

                    $excel->sheet('Attendance '.$date, function ($sheet) use ($student) {
                        $sheet->fromArray($student);
                    });

                })->download('xlsx');
        }

        if ($request['report_type'] == 'Attendance By Year') {

            $this->validate($request, [
                'school_id' => 'required',
                'report_type' => 'required',
                'yearfrom'=>'required',
            ]);

            if ($request['school_id'] != '' && $request['class_name'] != '' && $request['class_division'] != '') {
                $student = User::where('role', 'student')->where('school_id', $request['school_id'])->where('class_id', $request['class_name'])->where('division', $request['class_division'])->get();

            }
            else if ($request['school_id'] != '' && $request['class_name'] != '') {
                $student = User::where('role', 'student')->where('school_id', $request['school_id'])->where('class_id', $request['class_name'])->get();

            }
            else if($request['school_id'] != '') {

                $student = Student::where('role', 'student')->where('school_id', $request['school_id'])->get();
            }
            else{
                $student= new \Illuminate\Database\Eloquent\Collection;
            }

            if(count($student) == 0){
                Session::flash('message', 'No Record Found to Export!');
                Session::flash('alert-class', 'alert-danger');
                return redirect('report');
            }
            $holiday=Calendar::get()->toArray();

            $months=array('6'=>'June','7'=>'July','8'=>'August','9'=>'September','10'=>'October','11'=>'November','12'=>'December','1'=>'January','2'=>'February','3'=>'March','4'=>'April','5'=>'May');
            $year=$request['yearfrom'];

            foreach ($student as $key => $value) {
                $total_present_count=0;
                $total_working_days=0;
                $total_absent_count=0;
                $total_leave_count=0;
                $holiday_in_year=0;
                foreach ($months as $key1 => $value1) {
                    $month = $key1;
                    if ($month == '1') {
                        $year = $year + 1;
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

                $student[$key]['Present']=$total_present_count;
                $student[$key]['Absent']=$total_absent_count;
                $student[$key]['Leaves']=$total_leave_count;
                $student[$key]['Holidays']=$holiday_in_year;
                $student[$key]['Working Days']=$total_working_days;


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
            }

            Excel::create('Student '.$request['report_type'], function ($excel) use ($student,$date) {

                $excel->sheet('Attendance '.$date, function ($sheet) use ($student) {
                    $sheet->fromArray($student);
                });

            })->download('xlsx');
        }


    }
//    public function get_student($class_id,$school_id)
//    {
//        $student = Student::where('school_id',$school_id)->where('class_id',$class_id)->where('role','student')->get();
//        echo "<option value=''>Please Select</option>";
//        foreach ($student as $student1){
//            echo '<option value="'.$student1['id'].'">'.$student1['name'].'</option>';
//        }
//        return "";
//    }
//    public function get_classes($id)
//    {
//        $classes = Class_Master::where('school_id',$id)->get();
//        echo "<option value=''>Please Select</option>";
//        foreach ($classes as $class1){
//            echo '<option value="'.$class1['id'].'">'.$class1['name'].'</option>';
//        }
//
//        return "";
//    }
//    public function get_division($class_id,$school_id)
//    {
//        $classes = Division::where('school_id',$school_id)->where('class_id',$class_id)->get();
//        echo "<option value=''>Please Select</option>";
//        foreach ($classes as $class1){
//            echo '<option value="'.$class1['division'].'">'.$class1['division'].'</option>';
//        }
//
//        return "";
//    }

}
