<?php

namespace App\Http\Controllers\api;

use App\Attendance;
use App\AttendanceDetail;
use App\Calendar;
use App\Class_Master;
use App\Device;
use App\Http\Controllers\ReportController;
use App\Notification;
use App\ParentChild;
use App\School;
use App\Student;
use App\User;
use Carbon\Carbon;
use Cron\YearField;
use Edujugon\PushNotification\Facades\PushNotification;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class UserController extends Controller
{
//    const MONTH_JUNE = '06';
//    const MONTH_JULY = '07';
//    const MONTH_AUG = '08';
//    const MONTH_SEP = '09';
//    const MONTH_OCT = '10';
//    const MONTH_NOV = '11';
//    const MONTH_DEC = '12';
//    const MONTH_JAN = '01';
//    const MONTH_FEB = '02';
//    const MONTH_MAR = '03';
//    const MONTH_APR = '04';
//    const MONTH_MAY = '05';
//
//    public static $month = [
//        self:: MONTH_JUNE => 'June',
//        self:: MONTH_JULY => 'July',
//        self:: MONTH_AUG => 'August',
//        self:: MONTH_SEP => 'September',
//        self:: MONTH_OCT => 'October',
//        self:: MONTH_NOV => 'November',
//        self:: MONTH_DEC => 'December',
//        self:: MONTH_JAN => 'Januaray',
//        self:: MONTH_FEB => 'February',
//        self:: MONTH_MAR => 'March',
//        self:: MONTH_APR => 'April',
//        self:: MONTH_MAY => 'May',
//    ];

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                    'email' => 'required',
                    'password' => 'required',
                    'deviceToken' => 'required',
                ]
            );

            if ($validator->fails()) {
                $response["Result"] = 0;
                $response["Message"] = implode(',', $validator->errors()->all());
                return response($response, 200);
            }

            if (Auth::attempt(array('email' => $request['email'], 'password' => $request['password']))) {

                $user = Auth::user();

                if ($user->status == 'inactive') {
                    $response["Result"] = 0;
                    $response["Message"] = "Your account is deactivated, please contact administrator for further information.";
                    return response($response, 200);
                }

                $user['session_token'] = str_random(30);
                $user['deviceToken']=$request['deviceToken'];
                $user['device_type']='android';
                $user->save();

                $response["Result"] = 1;
                $response['User'] = $user;
                $response["Message"] = "Login Successful.";
                return response($response, 200);

            } else {
                $response["Result"] = 0;
                $response["Message"] = "Email address or password is incorrect. Please try again.";
                return response($response, 200);
            }
        } catch (Exception $e) {
            return response("", 500);
        }
    }

    public function get_all_school(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'session_token' => 'required',
            ]);
            if ($validator->fails()) {
                $response["Result"] = 0;
                $response["Message"] = implode(',', $validator->errors()->all());
                return response($response, 200);
            } else {
                $appuser = User::where("session_token", $request['session_token'])->first();
            }
            if (empty($appuser) || $request['session_token'] == "") {
                $response["Result"] = 0;
                $response["Message"] = "Invalid Session Token";
                return response($response, 200);
            }
            if (!empty($appuser)) {
                if ($appuser->role != 'admin') {
                    $response["Result"] = 0;
                    $response["Message"] = "Unauthorized User";
                    return response($response, 200);
                }
                $today = Carbon::today()->toDateString();

                $school = School::get();
                foreach ($school as $key => $value) {
                   if($value->image != ''){
                       $value->image=url($value->image);
                   }
                    else{
                        $value->image="";
                    }
                    unset($value->deleted_at);

                    /*counting total and attendance for a every school*/
                    $parents = User::select('id')->where('role','parent')->where('school_id', $value->id)->get();
                    $students = User::select('id')->where('role','student')->where('school_id', $value->id)->get();
                    $staffs = User::select('id')->where('role','staff')->where('school_id', $value->id)->get();

                    $teacher = User::select('id')->where('role','staff')->where('staff_role','teacher')->where('school_id', $value->id)->get();
                    $accountant = User::select('id')->where('role','staff')->where('staff_role','accountant')->where('school_id', $value->id)->get();
                    $peon = User::select('id')->where('role','staff')->where('staff_role','peon')->where('school_id', $value->id)->get();

                    $total_member=User::select('id')->where('role','!=','parent')->where('school_id', $value->id)->get();

                    $scount = count($students);
                    $pcount = count($parents);
                    $tcount = count($staffs);
                    $t_member_count=count($total_member);
                    $teacher_count=count($teacher);
                    $accountant_count=count($accountant);
                    $peon_count=count($peon);

                    $sp_count = 0;
                    $tp_count = 0;
                    $teacher_p_count=0;
                    $accountant_p_count=0;
                    $peon_p_count=0;

                    if($scount > 0) {
                        $spresent = Attendance::select('id')->where('attendance_date', $today)->whereIn('student_id', $students)->get()->all();
                        $sp_count = count($spresent);
                    }

                    if($tcount > 0) {
                        $tpresent = Attendance::select('id')->where('attendance_date', $today)->whereIn('staff_id', $staffs)->get()->all();
                        $tp_count = count($tpresent);
                    }
                    if($teacher_count > 0){
                        $teacherpresent = Attendance::select('id')->where('attendance_date', $today)->whereIn('staff_id', $teacher)->get()->all();
                        $teacher_p_count = count($teacherpresent);
                    }

                    if($accountant_count > 0){
                        $accountant_present = Attendance::select('id')->where('attendance_date', $today)->whereIn('staff_id', $accountant)->get()->all();
                        $accountant_p_count = count($accountant_present);
                    }

                    if($peon_count > 0){
                        $peon_present = Attendance::select('id')->where('attendance_date', $today)->whereIn('staff_id', $peon)->get()->all();
                        $peon_p_count = count($peon_present);
                    }
                    $t_member_present=$sp_count+$tp_count;

                    $school[$key]['total_member']=$t_member_count;
                    $school[$key]['total_member_present']=$t_member_present;
                    $school[$key]['total_member_absent']=$t_member_count - $t_member_present;
                    $school[$key]['parents']=$pcount;
                    $school[$key]['staff']=$tcount;
                    $school[$key]['staff_present']=$tp_count;
                    $school[$key]['staff_absent']=$tcount - $tp_count;
                    $school[$key]['teacher']=$teacher_count;
                    $school[$key]['teacher_present']=$teacher_p_count;
                    $school[$key]['teacher_absent']=$teacher_count - $teacher_p_count;

                    $school[$key]['accountant']=$accountant_count;
                    $school[$key]['accountant_present']=$accountant_p_count;
                    $school[$key]['accountant_absent']=$accountant_count - $accountant_p_count;

                    $school[$key]['peon']=$peon_count;
                    $school[$key]['peon_present']=$peon_p_count;
                    $school[$key]['peon_absent']=$peon_count-$peon_p_count;

                    $school[$key]['student']=$scount;
                    $school[$key]['student_present']=$sp_count;
                    $school[$key]['student_absent']=$scount-$sp_count;
                }

                $response["Result"] = 1;
                $response["Data"] = $school;
                $response["Message"] = "Success";
                return response($response, 200);
            }

        } catch (Exception $e) {
            return response("", 500);

        }
    }

    public function get_school_detail(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'session_token' => 'required',
            ]);
            if ($validator->fails()) {
                $response["Result"] = 0;
                $response["Message"] = implode(',', $validator->errors()->all());
                return response($response, 200);
            } else {
                $appuser = User::where("session_token", $request['session_token'])->first();
            }
            if (empty($appuser) || $request['session_token'] == "") {
                $response["Result"] = 0;
                $response["Message"] = "Invalid Session Token";
                return response($response, 200);
            }
            if (!empty($appuser)) {
                if ($appuser->role == 'admin' || $appuser->role == 'staff') {

                    if($appuser->role == 'admin') {
                        $school_id = $request['school_id'];
                    }
                    else{
                        $school_id=$appuser->school_id;
                    }

                    $today = Carbon::today()->toDateString();
                    /*here it return single record but due to android requirement use get()instead of first()*/
                     $getschool = School::where('id',$school_id)->get();
                      foreach($getschool as  $school){
                        if($school->image != ''){
                            $school->image=url($school->image);
                        }
                        else{
                            $school->image="";
                        }
                        unset($school->deleted_at);

                        /*counting total and attendance for a every school*/

                        $parents = User::select('id')->where('role','parent')->where('school_id', $school->id)->get();
                        $students = User::select('id')->where('role','student')->where('school_id', $school->id)->get();
                        $staffs = User::select('id')->where('role','staff')->where('school_id', $school->id)->get();

                        $teacher = User::select('id')->where('role','staff')->where('staff_role','teacher')->where('school_id', $school->id)->get();
                        $accountant = User::select('id')->where('role','staff')->where('staff_role','accountant')->where('school_id', $school->id)->get();
                        $peon = User::select('id')->where('role','staff')->where('staff_role','peon')->where('school_id', $school->id)->get();

                        $total_member=User::select('id')->where('role','!=','parent')->where('school_id', $school->id)->get();

                        $scount = count($students);
                        $pcount = count($parents);
                        $tcount = count($staffs);
                        $t_member_count=count($total_member);
                        $teacher_count=count($teacher);
                        $accountant_count=count($accountant);
                        $peon_count=count($peon);

                        $sp_count = 0;
                        $tp_count = 0;
                        $teacher_p_count=0;
                        $accountant_p_count=0;
                        $peon_p_count=0;

                        if($scount > 0) {
                            $spresent = Attendance::select('id')->where('attendance_date', $today)->whereIn('student_id', $students)->get()->all();
                            $sp_count = count($spresent);
                        }

                        if($tcount > 0) {
                            $tpresent = Attendance::select('id')->where('attendance_date', $today)->whereIn('staff_id', $staffs)->get()->all();
                            $tp_count = count($tpresent);
                        }
                        if($teacher_count > 0){
                            $teacherpresent = Attendance::select('id')->where('attendance_date', $today)->whereIn('staff_id', $teacher)->get()->all();
                            $teacher_p_count = count($teacherpresent);
                        }

                        if($accountant_count > 0){
                            $accountant_present = Attendance::select('id')->where('attendance_date', $today)->whereIn('staff_id', $accountant)->get()->all();
                            $accountant_p_count = count($accountant_present);
                        }

                        if($peon_count > 0){
                            $peon_present = Attendance::select('id')->where('attendance_date', $today)->whereIn('staff_id', $peon)->get()->all();
                            $peon_p_count = count($peon_present);
                        }
                        $t_member_present=$sp_count+$tp_count;

                        $school['total_member']=$t_member_count;
                        $school['total_member_present']=$t_member_present;
                        $school['total_member_absent']=$t_member_count - $t_member_present;
                        $school['parents']=$pcount;
                        $school['staff']=$tcount;
                        $school['staff_present']=$tp_count;
                        $school['staff_absent']=$tcount - $tp_count;
                        $school['teacher']=$teacher_count;
                        $school['teacher_present']=$teacher_p_count;
                        $school['teacher_absent']=$teacher_count - $teacher_p_count;

                        $school['accountant']=$accountant_count;
                        $school['accountant_present']=$accountant_p_count;
                        $school['accountant_absent']=$accountant_count - $accountant_p_count;

                        $school['peon']=$peon_count;
                        $school['peon_present']=$peon_p_count;
                        $school['peon_absent']=$peon_count-$peon_p_count;

                        $school['student']=$scount;
                        $school['student_present']=$sp_count;
                        $school['student_absent']=$scount-$sp_count;
                   }
                }
                else{
                    $response["Result"] = 0;
                    $response["Message"] = "Unauthorized User";
                    return response($response, 200);
                }

                $response["Result"] = 1;
                $response["Data"] = $getschool;
                $response["Message"] = "Success";
                return response($response, 200);
            }

        } catch (Exception $e) {
            return response("", 500);

        }
    }

    public function get_classes(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'session_token' => 'required',
                'school_id' => 'required',
            ]);
            if ($validator->fails()) {
                $response["Result"] = 0;
                $response["Message"] = implode(',', $validator->errors()->all());
                return response($response, 200);
            } else {
                $appuser = User::where("session_token", $request['session_token'])->first();
            }
            if (empty($appuser) || $request['session_token'] == "") {
                $response["Result"] = 0;
                $response["Message"] = "Invalid Session Token";
                return response($response, 200);
            }
            if (!empty($appuser)) {
                if ($appuser->role == 'admin' || $appuser->role == 'staff') {
                    $today = Carbon::today()->toDateString();
                     $class = Class_Master::with('Division')->where('school_id',$request['school_id'])->get();
                     foreach($class as $key => $value){
                         foreach($value['Division'] as $key1=> $value1){
                             $students = User::select('id')->where('role','student')->where('school_id',$value1->school_id)->where('class_id',$value1->class_id)->where('division',$value1->division)->get();
                             $scount = count($students);
                             $sp_count = 0;
                             if($scount > 0) {
                                 $spresent = Attendance::select('id')->where('attendance_date', $today)->whereIn('student_id', $students)->get()->all();
                                 $sp_count = count($spresent);
                             }
                             $value['Division'][$key1]['student']=$scount;
                             $value['Division'][$key1]['student_present']=$sp_count;
                             $value['Division'][$key1]['student_absent']=$scount-$sp_count;
                         }
                     }

                    }
                else{
                    $response["Result"] = 0;
                    $response["Message"] = "Unauthorized User";
                    return response($response, 200);
                }

                $response["Result"] = 1;
                $response["Data"] = $class;
                $response["Message"] = "Success";
                return response($response, 200);
            }

        } catch (Exception $e) {
            return response("", 500);

        }
    }


    public function get_students(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'session_token' => 'required',
                'school_id' => 'required',
                //'class_id' => 'required',
                //'division' => 'required',
            ]);
            if ($validator->fails()) {
                $response["Result"] = 0;
                $response["Message"] = implode(',', $validator->errors()->all());
                return response($response, 200);
            } else {
                $appuser = User::where("session_token", $request['session_token'])->first();
            }
            if (empty($appuser) || $request['session_token'] == "") {
                $response["Result"] = 0;
                $response["Message"] = "Invalid Session Token";
                return response($response, 200);
            }
            if (!empty($appuser)) {
                if ($appuser->role == 'admin' || $appuser->role == 'staff') {
                    $today = Carbon::today()->toDateString();
                   // $class = Class_Master::with('Division')->where('school_id',$request['school_id'])->get();
                     //$students = Student::where('role','student')->where('school_id',$request['school_id'])->where('class_id',$request['class_id'])->where('division',$request['division'])->get();

                    if($request['school_id'] != '' && $request['class_id'] != '' && $request['division'] != '') {
                        $students = Student::where('role', 'student')->where('school_id', $request['school_id'])->where('class_id', $request['class_id'])->where('division', $request['division'])->get();
                    }
                    else if($request['school_id'] != '' && $request['class_id'] != ''){
                        $students = Student::where('role', 'student')->where('school_id', $request['school_id'])->where('class_id', $request['class_id'])->get();
                    }
                    else if($request['school_id'] != ''){
                        $students = Student::where('role','student')->where('school_id', $request['school_id'])->get();
                    }
                    else{
                        $students=new \Illuminate\Database\Eloquent\Collection;
                    }
                    foreach($students as $key => $value){
                        $st_present = Attendance::select('id')->where('attendance_date', $today)->where('student_id',$value->id)->first();
                        if(!empty($st_present)){
                            $is_present=1;
                        }
                        else{
                            $is_present=0;
                        }
                        $students[$key]['is_present']=$is_present;
                    }
                }
                else{
                    $response["Result"] = 0;
                    $response["Message"] = "Unauthorized User";
                    return response($response, 200);
                }

                $response["Result"] = 1;
                $response["Data"] = $students;
                $response["Message"] = "Success";
                return response($response, 200);
            }

        } catch (Exception $e) {
            return response("", 500);

        }
    }

//    public function get_present_absent(Request $request)
//    {
//        try {
//            $validator = Validator::make($request->all(), [
//                'session_token' => 'required',
//                'school_id' => 'required',
//                //'class_id' => 'required',
//                //'division' => 'required',
//            ]);
//            if ($validator->fails()) {
//                $response["Result"] = 0;
//                $response["Message"] = implode(',', $validator->errors()->all());
//                return response($response, 200);
//            } else {
//                $appuser = User::where("session_token", $request['session_token'])->first();
//            }
//            if (empty($appuser) || $request['session_token'] == "") {
//                $response["Result"] = 0;
//                $response["Message"] = "Invalid Session Token";
//                return response($response, 200);
//            }
//            if (!empty($appuser)) {
//                $present_student=new \Illuminate\Database\Eloquent\Collection;
//                $absent_student=new \Illuminate\Database\Eloquent\Collection;
//                if ($appuser->role == 'admin' || $appuser->role == 'staff') {
//
//                    $today = Carbon::today()->toDateString();
//
//                    if($request['school_id'] != '' && $request['class_id'] != '' && $request['division'] != '') {
//                        $students = Student::where('role', 'student')->where('school_id', $request['school_id'])->where('class_id', $request['class_id'])->where('division', $request['division'])->get();
//                    }
//                    else if($request['school_id'] != '' && $request['class_id'] != ''){
//                        $students = Student::where('role', 'student')->where('school_id', $request['school_id'])->where('class_id', $request['class_id'])->get();
//                    }
//                    else if($request['school_id'] != ''){
//                        $students = Student::where('role','student')->where('school_id', $request['school_id'])->get();
//                    }
//                    else{
//                        $students=new \Illuminate\Database\Eloquent\Collection;
//                    }
//                    foreach($students as $key => $value){
//                       // return $value;
//                        $st_present = Attendance::select('id')->where('attendance_date', $today)->where('student_id',$value->id)->first();
//                        if(!empty($st_present)){
//                            $present_student->push($value);
//                        }
//                        else{
//                            $absent_student->push($value);
//                        }
//                    }
//                }
//                else{
//                    $response["Result"] = 0;
//                    $response["Message"] = "Unauthorized User";
//                    return response($response, 200);
//                }
//
//                $response["Result"] = 1;
//                $response["Total"] = $students;
//                $response["Present"] = $present_student;
//                $response["Absent"] = $absent_student;
//                $response["Message"] = "Success";
//                return response($response, 200);
//            }
//
//        } catch (Exception $e) {
//            return response("", 500);
//
//        }
//    }

   public function get_year_report(Request $request){

       try {
            $validator = Validator::make($request->all(), [
                'session_token' => 'required',
                'id' => 'required',
            ]);
            if ($validator->fails()) {
                $response["Result"] = 0;
                $response["Message"] = implode(',', $validator->errors()->all());
                return response($response, 200);
            } else {
                $appuser = User::where("session_token", $request['session_token'])->first();
            }
            if (empty($appuser) || $request['session_token'] == "") {
                $response["Result"] = 0;
                $response["Message"] = "Invalid Session Token";
                return response($response, 200);
            }
            if (!empty($appuser)) {

              //  if ($appuser->role == 'admin' || $appuser->role == 'staff'){
                    $full_detail = new \Illuminate\Database\Eloquent\Collection;

                    $student = Student::where('id',$request['id'])->first();
                    $year = Carbon::today()->year;
                    $total_present_count=0;
                    $total_absent_count=0;
                    $total_working_days=0;
                    $total_leave_count=0;
                    $holiday=array();
                    $holidays=Calendar::where('school_id',$student->school_id)->get();
                    foreach ($holidays as $k => $hvalue){
                        array_push($holiday,$hvalue->holiday_date);
                    }
                    $months = array('6' => 'June', '7' => 'July', '8' => 'August', '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December', '1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April', '5' => 'May');
                     $data['title']=$student;

                    $get_detail = new \Illuminate\Database\Eloquent\Collection;

                    foreach($months as $key1 => $value1){
                        $month = $key1;
//                        if(Carbon::today()->month <=5){
//                            $year=Carbon::today()->year;
//                        }
//                        else{
//                            $year= Carbon::today()->year+1;
//                        }
                        if($month == '1'){
                            $year= Carbon::today()->year+1;
                        }
                        $start_date = "01-".$month."-".$year;
                        $start_time = strtotime($start_date);

                        $end_time = strtotime("+1 month", $start_time);
                        $present_count=0;
                        $absent_count=0;
                        $working_days=0;
                        $leave_count=0;
                        for($i=$start_time; $i<$end_time; $i+=86400)
                        {
                            if(date('N',$i) <= 6 && !in_array(date('Y-m-d', $i),$holiday)) {
                                $total_working_days++;
                                $working_days++;

                                $st_present = Attendance::where('attendance_date', date('Y-m-d', $i))->where('student_id', $request['id'])->first();
                                if (!empty($st_present)) {
                                    if($st_present->on_leave == 1){
                                        $leave_count++;
                                        $total_leave_count++;
                                    }
                                    else {
                                        $present_count++;
                                        $total_present_count++;
                                    }
                                } else {
                                    $absent_count++;
                                    $total_absent_count++;
                                }
                            }
                        }
                        $data1['Month']=$value1;
                        $data1['Present']=$present_count;
                        $data1['Absent']=$absent_count;
                        $data1['Working Days']=$working_days;
                        $data1['Leave']=$leave_count;

                        if($total_present_count != 0){
                            $percentage=$present_count * 100 / $working_days;
                        }
                        else{
                            $percentage=0;
                        }
                        $data1['Percentage']=$percentage;
                        $get_detail->add($data1);
                    }
                    $summary['Total_Present']=$total_present_count;
                    $summary['Total_Absent']=$total_absent_count;
                    $summary['Total_Leave']=$total_leave_count;
                    $summary['Total_Working_Days']=$total_working_days;
                    if($total_present_count != 0){
                        $total_percentage=$total_present_count * 100 /$total_working_days;
                    }
                    else{
                        $total_percentage=0;
                    }
                    $summary['Total_Percentage']=$total_percentage;
                    $data['Summary']=$summary;
                    $data['detail']=$get_detail;
                    $full_detail->add($data);
            //    }
//                else{
//                    $response["Result"] = 0;
//                    $response["Message"] = "Unauthorized User";
//                    return response($response, 200);
//                }
                $response["Result"] = 1;
                $response["Data"] = $full_detail;
                $response["Message"] = "Success";
                return response($response, 200);
            }

        } catch (Exception $e) {
            return response("", 500);
        }
  }


    public function get_month_report(Request $request){

        try {
            $validator = Validator::make($request->all(), [
                'session_token' => 'required',
                'id' => 'required',
                'date' => 'required',
            ]);
            if ($validator->fails()) {
                $response["Result"] = 0;
                $response["Message"] = implode(',', $validator->errors()->all());
                return response($response, 200);
            } else {
                $appuser = User::where("session_token", $request['session_token'])->first();
            }
            if (empty($appuser) || $request['session_token'] == "") {
                $response["Result"] = 0;
                $response["Message"] = "Invalid Session Token";
                return response($response, 200);
            }
            if (!empty($appuser)) {
              //  if ($appuser->role == 'admin' || $appuser->role == 'staff'){
                     $student = Student::where('id',$request['id'])->first();
                    $year = Carbon::parse($request['date'])->year;
                    $month = Carbon::parse($request['date'])->month;

                    $total_present_count=0;
                    $total_absent_count=0;
                    $total_working_days=0;
                    $total_leave_count=0;
                    $holiday=array();
                    $holidays=Calendar::where('school_id',$student->school_id)->get();
                    foreach ($holidays as $k => $hvalue){
                      array_push($holiday,$hvalue->holiday_date);
                    }

                    $data_by_date=new  \Illuminate\Database\Eloquent\Collection;
                        $start_date = "01-".$month."-".$year;
                        $start_time = strtotime($start_date);

                        $end_time = strtotime("+1 month", $start_time);

                        for($i=$start_time; $i<$end_time; $i+=86400)
                        {
                            if(date('N',$i) <= 6 && !in_array(date('Y-m-d', $i),$holiday)) {
                                $total_working_days++;

                                $st_present = Attendance::where('attendance_date', date('Y-m-d', $i))->where('student_id', $request['id'])->first();
                                if (!empty($st_present)) {
                                    if($st_present->on_leave == 1){
                                        $total_leave_count++;
                                        $data['date']=date('Y-m-d', $i);
                                        $data['is_present']=0;
                                        $data['is_holiday']=0;
                                        $data['on_leave']=1;
                                    }
                                    else{
                                        $total_present_count++;
                                        $data['date']=date('Y-m-d', $i);
                                        $data['is_present']=1;
                                        $data['is_holiday']=0;
                                        $data['on_leave']=0;
                                    }

                                } else {
                                    $total_absent_count++;
                                    $data['date']=date('Y-m-d', $i);
                                    $data['is_present']=0;
                                    $data['is_holiday']=0;
                                    $data['on_leave']=0;
                                }
                            }
                            else{
                                $data['date']=date('Y-m-d', $i);
                                $data['is_present']=0;
                                $data['is_holiday']=1;
                                $data['on_leave']=0;
                            }
                            $data_by_date->add($data);
                        }

                    $summary['Total_Present']=$total_present_count;
                    $summary['Total_Absent']=$total_absent_count;
                    $summary['Total_Leave']=$total_leave_count;
                    $summary['Total_Working_Days']=$total_working_days;

                    if($total_present_count != 0){
                        $total_percentage=$total_present_count * 100 /$total_working_days;
                    }
                    else{
                        $total_percentage=0;
                    }
                    $summary['Total_Percentage']=$total_percentage;
              //  }
//                else{
//                    $response["Result"] = 0;
//                    $response["Message"] = "Unauthorized User";
//                    return response($response, 200);
//                }
                $final['title']=$student;
                $final['detail']=$data_by_date;
                $final['summary']=$summary;
               // $return_arr = new \Illuminate\Database\Eloquent\Collection;
               // $return_arr->add($final);

                $response["Result"] = 1;
               // $response['data']=$return_arr;
               $response['data']=$final;
                $response["Message"] = "Success";
                return response($response, 200);
            }

        } catch (Exception $e) {
            return response("", 500);
        }
    }


    public function get_send_notification(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'session_token' => 'required',
            ]);
            if ($validator->fails()) {
                $response["Result"] = 0;
                $response["Message"] = "Invalid session_token ";
                return response($response, 200);
            }

            $appuser = User::where("session_token", $request['session_token'])->first();
            if (empty($appuser) || $request['session_token'] == "") {
                $response["Result"] = 0;
                $response["Message"] = "Your account may be logged in from other device or deactivated, please try to login again.";
                return response($response, 200);
            }
            if (!empty($appuser)) {
              //  $notification=Notification::where('receiver_id',$appuser->id)->where('sender_id','!=',$appuser->id)->where('type','notification')->orderBy('id','DESC')->get();
                $notification=Notification::where('sender_id',$appuser->id)->where('type','notification')->orderBy('id','DESC')->get();

//                foreach($notification as $key => $val)
//                {
//                    $user = User::where('id', $val->sender_id)->first();
//                    if ($user->image != "") {
//                        $user->image = url($user->image);
//                    }
//                     else {
//                        $user->image = "";
//                    }
//                    unset($user->deleted_at);
//                    unset($user->password);
//
//                    $notification[$key]['user']=$user;
//                }

                $response["Result"] = 1;
                $response["Data"] = $notification;
                $response["Message"] = "Success";
                return response($response, 200);
            }
        } catch (Exception $e) {
            return response("", 500);
        }

    }

    public function get_receive_notification(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'session_token' => 'required',
            ]);
            if ($validator->fails()) {
                $response["Result"] = 0;
                $response["Message"] = "Invalid session_token ";
                return response($response, 200);
            }

            $appuser = User::where("session_token", $request['session_token'])->first();
            if (empty($appuser) || $request['session_token'] == "") {
                $response["Result"] = 0;
                $response["Message"] = "Your account may be logged in from other device or deactivated, please try to login again.";
                return response($response, 200);
            }
            if (!empty($appuser)) {
                $notification=Notification::where('receiver_id',$appuser->id)->where('type','notification')->orderBy('id','DESC')->get();
//                foreach($notification as $key => $val)
//                {
//                    $user = User::where('id', $val->sender_id)->first();
//                    if ($user->image != "") {
//                        $user->image = url($user->image);
//                    }
//                    else {
//                        $user->image = "";
//                    }
//                    unset($user->deleted_at);
//                    unset($user->password);
//
//                    $notification[$key]['user']=$user;
//                }

                $response["Result"] = 1;
                $response["Data"] = $notification;
                $response["Message"] = "Success";
                return response($response, 200);
            }
        } catch (Exception $e) {
            return response("", 500);
        }

    }


    public function get_my_child(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'session_token' => 'required',
            ]);
            if ($validator->fails()) {
                $response["Result"] = 0;
                $response["Message"] = "Invalid session_token ";
                return response($response, 200);
            }

            $appuser = User::where("session_token", $request['session_token'])->first();
            if (empty($appuser) || $request['session_token'] == "") {
                $response["Result"] = 0;
                $response["Message"] = "Your account may be logged in from other device or deactivated, please try to login again.";
                return response($response, 200);
            }
            if (!empty($appuser)) {
                if($appuser->role != 'parent'){
                    $response["Result"] = 1;
                    $response["Message"] = "You are not parent";
                    return response($response, 200);
                }

                $mychildlist=new \Illuminate\Database\Eloquent\Collection;
                $parent_child=ParentChild::where('parent_id',$appuser->id)->get();
                foreach($parent_child as $key => $val)
                {
                    $student = User::where('role','student')->where('id', $val->student_id)->first();

                    $holiday=array();
                    $holidays=Calendar::where('school_id',$student->school_id)->get();
                    foreach ($holidays as $k => $hvalue){
                        array_push($holiday,$hvalue->holiday_date);
                    }
                    unset($student->deleted_at);
                    unset($student->password);
                    $school=School::select('name')->where('id',$student->school_id)->first();
                    $student->school_name=$school->name;
                    $class=Class_Master::select('name')->where('id',$student->class_id)->first();
                    $student->class_name=$class->name;
                    $today=Carbon::today()->format('Y-m-d');

                    if(date('N',strtotime($today)) <= 6 && !in_array($today,$holiday)) {

                        $attendance = Attendance::where('attendance_date', $today)->where('student_id', $student->id)->first();
                        if (!empty($attendance)) {
                            if ($attendance->on_leave == 1) {
                                $student['on_leave'] = 1;
                                $student['present'] = 0;
                                $student['is_holiday'] = 0;
                                $student['lat']='0.0';
                                $student['long']='0.0';

                            } else {
                                $student['on_leave'] = 0;
                                $student['present'] = 1;
                                $student['is_holiday'] = 0;
                                $student['lat']=$attendance->latittude;
                                $student['long']=$attendance->longitude;
                            }
                        } else {
                            $student['on_leave'] = 0;
                            $student['present'] = 0;
                            $student['is_holiday'] = 0;
                            $student['lat']='0.0';
                            $student['long']='0.0';

                        }
                    }
                    else{
                        $student['on_leave'] = 0;
                        $student['present'] = 0;
                        $student['is_holiday'] = 1;
                        $student['lat']='0.0';
                        $student['long']='0.0';

                    }
                    $mychildlist->add($student);
                }

                $response["Result"] = 1;
                $response["Data"] = $mychildlist;
                $response["Message"] = "Success";
                return response($response, 200);
            }
        } catch (Exception $e) {
            return response("", 500);
        }

    }

    public function get_device(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'session_token' => 'required',
                'school_id' => 'required',
            ]);
            if ($validator->fails()) {
                $response["Result"] = 0;
                $response["Message"] = "Invalid session_token ";
                return response($response, 200);
            }

            $appuser = User::where("session_token", $request['session_token'])->first();
            if (empty($appuser) || $request['session_token'] == "") {
                $response["Result"] = 0;
                $response["Message"] = "Your account may be logged in from other device or deactivated, please try to login again.";
                return response($response, 200);
            }
            if (!empty($appuser)) {
                $device=Device::where('school_id',$request['school_id'])->get();
                $response["Result"] = 1;
                $response["Data"] = $device;
                $response["Message"] = "Success";
                return response($response, 200);
            }
        } catch (Exception $e) {
            return response("", 500);
        }

    }

    public function get_device_month_report(Request $request){

        try {
            $validator = Validator::make($request->all(), [
                'session_token' => 'required',
                'id' => 'required',
                'date' => 'required',
                'device_id' => 'required',
            ]);
            if ($validator->fails()) {
                $response["Result"] = 0;
                $response["Message"] = implode(',', $validator->errors()->all());
                return response($response, 200);
            } else {
                $appuser = User::where("session_token", $request['session_token'])->first();
            }
            if (empty($appuser) || $request['session_token'] == "") {
                $response["Result"] = 0;
                $response["Message"] = "Invalid Session Token";
                return response($response, 200);
            }
            if (!empty($appuser)) {
                //  if ($appuser->role == 'admin' || $appuser->role == 'staff'){
                $student = Student::where('id',$request['id'])->first();
                $year = Carbon::parse($request['date'])->year;
                $month = Carbon::parse($request['date'])->month;

                $total_present_count=0;
                $total_absent_count=0;
                $total_working_days=0;
                $total_leave_count=0;
                $holiday=array();
                $holidays=Calendar::where('school_id',$student->school_id)->get();
                foreach ($holidays as $k => $hvalue){
                    array_push($holiday,$hvalue->holiday_date);
                }

                $data_by_date=new  \Illuminate\Database\Eloquent\Collection;
                $start_date = "01-".$month."-".$year;
                $start_time = strtotime($start_date);

                $end_time = strtotime("+1 month", $start_time);

                for($i=$start_time; $i<$end_time; $i+=86400)
                {
                    if(date('N',$i) <= 6 && !in_array(date('Y-m-d', $i),$holiday)) {
                        $total_working_days++;

                        $st_present = AttendanceDetail::where('device_id',$request['device_id'])->where('attendance_date', date('Y-m-d', $i))->where('student_id', $request['id'])->first();
                        if (!empty($st_present)) {
                            if($st_present->on_leave == 1){
                                $total_leave_count++;
                                $data['date']=date('Y-m-d', $i);
                                $data['is_present']=0;
                                $data['is_holiday']=0;
                                $data['on_leave']=1;
                            }
                            else{
                                $total_present_count++;
                                $data['date']=date('Y-m-d', $i);
                                $data['is_present']=1;
                                $data['is_holiday']=0;
                                $data['on_leave']=0;
                            }

                        } else {
                            $total_absent_count++;
                            $data['date']=date('Y-m-d', $i);
                            $data['is_present']=0;
                            $data['is_holiday']=0;
                            $data['on_leave']=0;
                        }
                    }
                    else{
                        $data['date']=date('Y-m-d', $i);
                        $data['is_present']=0;
                        $data['is_holiday']=1;
                        $data['on_leave']=0;
                    }
                    $data_by_date->add($data);
                }

                $summary['Total_Present']=$total_present_count;
                $summary['Total_Absent']=$total_absent_count;
                $summary['Total_Leave']=$total_leave_count;
                $summary['Total_Working_Days']=$total_working_days;

                if($total_present_count != 0){
                    $total_percentage=$total_present_count * 100 /$total_working_days;
                }
                else{
                    $total_percentage=0;
                }
                $summary['Total_Percentage']=$total_percentage;

                $final['title']=$student;
                $final['detail']=$data_by_date;
                $final['summary']=$summary;

                $response["Result"] = 1;
                $response['data']=$final;
                $response["Message"] = "Success";
                return response($response, 200);
            }

        } catch (Exception $e) {
            return response("", 500);
        }
    }
            public function test_notification(Request $request){

//                   $a= \Davibennun\LaravelPushNotification\Facades\PushNotification::app('appNameAndroid')
//                       ->to('feyVriUPVz8:APA91bECPaymlADpggozluJEOx0j2WZ8ptCoj1eLwjOdu-R4xVaJs56aUqmL8FXiA1qlrgJAjyj4JDsU9TrodSm4DCKB7lWeFNcmiwhPFfJdfmyNRcqWwsdPaaYg61s23OGTAMhk3p01')
//                        ->send('Testing msg');
//                $response["test"]=$a;
//                $response["Message"] = "success";
//                return response($response, 200);

               $a= PushNotification::setService('fcm')
                    ->setMessage([
                        'notification' => [
                            'title'=>'This is the title',
                            'body'=>'This is the message',
                            'sound' => 'default'
                        ],
                        'data' => [
                            'extraPayLoad1' => 'value1',
                            'extraPayLoad2' => 'value2'
                        ]
                    ])
                    ->setApiKey('AAAANfHw2Ls:APA91bEvYnZf_EHIqdrjyW-aPN-XpTZXt6bHeigfJATId9IKDCZqh6hLppD5CvN31yYCQPYzh5SYOK4ZscFF7rI7oR1TeS2w5cBpGZB0eGCWOsnw1MyyTx4os4F3jdKo_4opnXZrWxIY')
                     ->setDevicesToken(['feyVriUPVz8:APA91bECPaymlADpggozluJEOx0j2WZ8ptCoj1eLwjOdu-R4xVaJs56aUqmL8FXiA1qlrgJAjyj4JDsU9TrodSm4DCKB7lWeFNcmiwhPFfJdfmyNRcqWwsdPaaYg61s23OGTAMhk3p01'])
                        ->send()
                    ->getFeedback();
              print_r($a);
//                $push = new PushNotification('fcm');
//                $push->setMessage(['body'=>'This is the message','title'=>'This is the title'])
//                    ->setApiKey('AAAANfHw2Ls:APA91bEvYnZf_EHIqdrjyW-aPN-XpTZXt6bHeigfJATId9IKDCZqh6hLppD5CvN31yYCQPYzh5SYOK4ZscFF7rI7oR1TeS2w5cBpGZB0eGCWOsnw1MyyTx4os4F3jdKo_4opnXZrWxIY')
//                    ->setDevicesToken(['feyVriUPVz8:APA91bECPaymlADpggozluJEOx0j2WZ8ptCoj1eLwjOdu-R4xVaJs56aUqmL8FXiA1qlrgJAjyj4JDsU9TrodSm4DCKB7lWeFNcmiwhPFfJdfmyNRcqWwsdPaaYg61s23OGTAMhk3p01'])
//                    ->send()
//                    ->getFeedback();
            }

    public function test_sms(){

          //  $URL = "http://bulksms.alayada.com/vendorsms/pushsms.aspx?user=chetanjsheth&password=C123123H1212&msisdn=8200052556&sid=ALAIPL&msg=Dear Parents , Your Child ABC has come to school at 11:00 &fl=0&gwid=2";
           // $data = file_get_contents($URL);
            $ch = curl_init();// init curl
            curl_setopt($ch, CURLOPT_URL,"http://bulksms.alayada.com/vendorsms/pushsms.aspx");
            curl_setopt($ch, CURLOPT_POST, 1);// set post data to true
        /*transactional*/
            //curl_setopt($ch, CURLOPT_POSTFIELDS,"user=chetanjsheth&password=C123123H1212&msisdn=9979584783&sid=ALAIPL&msg=Dear Parents , Your Child Chirag janjmera has come to school at 9:30am 23 March 2017.&fl=0&gwid=2");// post data
        /*
         Promotional*/

         curl_setopt($ch, CURLOPT_POSTFIELDS,"user=chetanjsheth&password=C123123H1212&msisdn=9979584783&sid=WEBSMS&msg=Dear Parents , Your Child Chirag janjmera has come to school at 9:30am 23 March 2017.&fl=0");// post data

            // receive server response ...
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);// gives you a response from the server 

            $response = curl_exec ($ch);// response it ouputed in the response var

            curl_close ($ch);// close curl connection
        return $response;
    }
}