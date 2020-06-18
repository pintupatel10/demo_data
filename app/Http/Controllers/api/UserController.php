<?php

namespace App\Http\Controllers\api;

use App\Attendance;
use App\AttendanceDetail;
use App\Calendar;
use App\Class_Master;
use App\Device;
use App\Division;
use App\Http\Controllers\ReportController;
use App\Notification;
use App\ParentChild;
use App\School;
use App\Staff;
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

                $school=School::where('id',$user['school_id'])->first();
                if(!empty($school)){
                    $user['school_name']=$school->name;
                }
                else{
                    $user['school_name']='';
                }

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

    public function reset_password(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'session_token' => 'required',
                'password' => 'required|confirmed',
                'password_confirmation' => 'required',
            ]);
            if ($validator->fails()) {
                $response["Result"] = 0;
                $response["Message"] = implode(',', $validator->errors()->all());
                return response($response, 200);
            }
            else {
                $appuser = User::where("session_token", $request['session_token'])->first();
            }
            if (empty($appuser) || $request['session_token'] == "") {
                $response["Result"] = 0;
                $response["Message"] = "Invalid Session Token";
                return response($response, 200);
            }
            if (!empty($appuser)) {
                $appuser['password']=bcrypt($request['password']);
                $appuser->save();
                $response["Result"] = 1;
                $response["Message"] = "Password updated successfully.";
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

    public function get_classes_simple(Request $request)
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
                $class = Class_Master::where('school_id',$request['school_id'])->get();

                $response["Result"] = 1;
                $response["Data"] = $class;
                $response["Message"] = "Success";
                return response($response, 200);
            }

        } catch (Exception $e) {
            return response("", 500);

        }
    }

    public function get_division(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'session_token' => 'required',
                'school_id' => 'required',
                'class_id' => 'required',

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
                   $division=Division::where('school_id',$request['school_id'])->where('class_id',$request['class_id'])->get();

                $response["Result"] = 1;
                $response["Data"] = $division;
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

                $holiday = Calendar::where('school_id',$student->school_id)->where('status','active')->pluck('holiday_date')->toArray();


                    $months = array('6' => 'Jun', '7' => 'Jul', '8' => 'Aug', '9' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec', '1' => 'Jan', '2' => 'Feb', '3' => 'Mar', '4' => 'Apr', '5' => 'May');
                     $data['title']=$student;

                    $get_detail = new \Illuminate\Database\Eloquent\Collection;

                    foreach($months as $key1 => $value1){
                        $month = $key1;

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
                            if(!in_array(date('Y-m-d', $i),$holiday)) {
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

                        $data1['Percentage']=doubleval(number_format($percentage,2));
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
                    $summary['Total_Percentage']=doubleval(number_format($total_percentage,2));
               // return gettype($summary['Total_Percentage']);
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

                    $holiday = Calendar::where('school_id',$student->school_id)->where('status','active')->pluck('holiday_date')->toArray();

                    $data_by_date=new  \Illuminate\Database\Eloquent\Collection;
                        $start_date = "01-".$month."-".$year;
                        $start_time = strtotime($start_date);

                        $end_time = strtotime("+1 month", $start_time);

                        for($i=$start_time; $i<$end_time; $i+=86400)
                        {
                            if(!in_array(date('Y-m-d', $i),$holiday)) {
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
                    $summary['Total_Percentage']=doubleval(number_format($total_percentage,2));
               // return gettype( $summary['Total_Percentage']);

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

                    $holiday = Calendar::where('school_id',$student->school_id)->where('status','active')->pluck('holiday_date')->toArray();

                    unset($student->deleted_at);
                    unset($student->password);

                    $school=School::select('name')->where('id',$student->school_id)->first();
                    $student->school_name=$school->name;
                    $class=Class_Master::select('name')->where('id',$student->class_id)->first();
                    $student->class_name=$class->name;
                    $today=Carbon::today()->format('Y-m-d');

                    if(!in_array($today,$holiday)) {

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

    public function get_device_report_student(Request $request){

        try {
            $validator = Validator::make($request->all(), [
                'session_token' => 'required',
                'student_id' => 'required',
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
                $student = Student::where('id',$request['student_id'])->first();
                if(!empty($request['from'])){
                   $from= $request['from'];
                }
                else{
                    $from=Carbon::now();
                }
                if(!empty($request['to'])){
                    $to= $request['to'];
                }
                else{
                    $to=Carbon::now();
                }
                $start_date = Carbon::parse($from)->format('Y-m-d');
                $end_date=Carbon::parse($to)->format('Y-m-d');

                $check_student = AttendanceDetail::where('device_id',$request['device_id'])->where('attendance_date','>=',$start_date)->where('attendance_date','<=',$end_date)->where('student_id', $request['student_id'])->get();

//                $start_time = strtotime($start_date);
//                $end_time = strtotime($end_date);
//                 $final=array();
//                for($i=$start_time; $i<$end_time; $i+=86400)
//                {
//                        $check_student = AttendanceDetail::where('device_id',$request['device_id'])->where('attendance_date', date('Y-m-d', $i))->where('student_id', $request['student_id'])->first();
//                        if (!empty($check_student)) {
//                          array_push($final,$check_student);
//                        }
//                }

                $response["Result"] = 1;
                $response['data']=$check_student;
                $response["Message"] = "Success";
                return response($response, 200);
            }

        } catch (Exception $e) {
            return response("", 500);
        }
    }


    public function get_attendance_report(Request $request){

        try {
            $validator = Validator::make($request->all(), [
                'session_token' => 'required',
                'school_id' => 'required',
                'device_id' => 'required',
                //'report'=>'required',
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

              //  $staff = User::where('id',$request['staff_id'])->first();

                if(!empty($request['from'])){
                    $from= $request['from'];
                }
                else{
                    $from=Carbon::now();
                }
                if(!empty($request['to'])){
                    $to= $request['to'];
                }
                else{
                    $to=Carbon::now();
                }
                $start_date = Carbon::parse($from)->format('Y-m-d');
                $end_date=Carbon::parse($to)->format('Y-m-d');

                if(empty($request['report'])){
                    $report="none";
                }
                else{
                    $report=$request['report'];
                }
                $attendance=new \Illuminate\Database\Eloquent\Collection;
                if($report == 'none'){
                    $attendance= Attendance::where('device_id',$request['device_id'])->where('school_id',$request['school_id'])
                        ->where('attendance_date','>=',$start_date)->where('attendance_date','<=',$end_date)->get();
                }
                if($report == 'student'){
                    $attendance = Attendance::where('device_id',$request['device_id'])->where('school_id',$request['school_id'])
                        ->where('attendance_date','>=',$start_date)->where('attendance_date','<=',$end_date)
                        ->where('student_id', '!=',0)->where('staff_id',0)->get();
                }
                if($report == 'staff'){
                    $attendance = Attendance::where('device_id',$request['device_id'])->where('school_id',$request['school_id'])
                        ->where('attendance_date','>=',$start_date)->where('attendance_date','<=',$end_date)
                        ->where('staff_id', '!=',0)->where('student_id',0)->get();
                }

                $response["Result"] = 1;
                $response['data']=$attendance;
                $response["Message"] = "Success";
                return response($response, 200);
            }

        } catch (Exception $e) {
            return response("", 500);
        }
    }

    public function get_student_attendance_for_teacher(Request $request){

        try {
            $validator = Validator::make($request->all(), [
                'session_token' => 'required',
                'school_id' => 'required',
                'device_id' => 'required',
                'class_id'=>'required',
                //'report'=>'required',
            ]);

            if ($validator->fails()) {
                $response["Result"] = 0;
                $response["Message"] = implode(',', $validator->errors()->all());
                return response($response, 200);
            }
            else {
                $appuser = User::where("session_token", $request['session_token'])->first();
            }
            if (empty($appuser) || $request['session_token'] == "") {
                $response["Result"] = 0;
                $response["Message"] = "Invalid Session Token";
                return response($response, 200);
            }
            if (!empty($appuser)) {

                //  $staff = User::where('id',$request['staff_id'])->first();

                if(!empty($request['from'])){
                    $from= $request['from'];
                }
                else{
                    $from=Carbon::now();
                }
                if(!empty($request['to'])){
                    $to= $request['to'];
                }
                else{
                    $to=Carbon::now();
                }
                $start_date = Carbon::parse($from)->format('Y-m-d');
                $end_date=Carbon::parse($to)->format('Y-m-d');

                $attendance=new \Illuminate\Database\Eloquent\Collection;

                $class=Class_Master::where('id',$request['class_id'])->where('school_id',$request['school_id'])->first();
                if(!empty($request['division'])){
                    $attendance = Attendance::where('device_id',$request['device_id'])->where('school_id',$request['school_id'])
                        ->where('class_name',$request['class_id'])->where('class_division',$request['division'])
                        ->where('attendance_date','>=',$start_date)->where('attendance_date','<=',$end_date)
                        ->where('student_id', '!=',0)->where('staff_id',0)->get();
                }
                else{
                    $attendance = Attendance::where('device_id',$request['device_id'])->where('school_id',$request['school_id'])
                        ->where('class_name',$request['class_id'])
                        ->where('attendance_date','>=',$start_date)->where('attendance_date','<=',$end_date)
                        ->where('student_id', '!=',0)->where('staff_id',0)->get();
                }

                foreach($attendance as $key=>$value){
                    $attendance[$key]['class_name']=$class['name'];
                }
                $response["Result"] = 1;
                $response['data']=$attendance;
                $response["Message"] = "Success";
                return response($response, 200);
            }

        } catch (Exception $e) {
            return response("", 500);
        }
    }

    public function get_present_student_list(Request $request){

        try {
            $validator = Validator::make($request->all(), [
                'session_token' => 'required',
                'school_id' => 'required',
            ]);

            if ($validator->fails()) {
                $response["Result"] = 0;
                $response["Message"] = implode(',', $validator->errors()->all());
                return response($response, 200);
            }
            else {
                $appuser = User::where("session_token", $request['session_token'])->first();
            }
            if (empty($appuser) || $request['session_token'] == "") {
                $response["Result"] = 0;
                $response["Message"] = "Invalid Session Token";
                return response($response, 200);
            }
            if (!empty($appuser)) {

                if(!empty($request['date'])){
                    $date=Carbon::parse($request['date'])->format('Y-m-d');
                }
                else{
                    $date=Carbon::today()->format('Y-m-d');
                }

                if(!empty($request['class_id']) && !empty($request['division'])) {
                    $attendance_list = Attendance::where('attendance_date', $date)->where('school_id', $request['school_id'])->where('class_name', $request['class_id'])->where('class_division', $request['division'])->where('student_id', '!=', 0)->lists('student_id');
                }
                else if(!empty($request['class_id'])){
                    $attendance_list = Attendance::where('attendance_date', $date)->where('school_id', $request['school_id'])->where('class_name', $request['class_id'])->where('student_id', '!=', 0)->lists('student_id');

                }
                else{
                    $attendance_list = Attendance::where('attendance_date', $date)->where('school_id', $request['school_id'])->where('student_id', '!=', 0)->lists('student_id');
                }

                $student=Student::where('role','student')->whereIn('id',$attendance_list)->get();

                foreach ($student as $key=> $value) {
                    unset($student[$key]['password']);
                    unset($student[$key]['deleted_at']);

                }

                $response["Result"] = 1;
                $response['data']=$student;
                $response["Message"] = "Success";
                return response($response, 200);
            }
        } catch (Exception $e) {
            return response("", 500);
        }
    }

    public function get_absent_student_list(Request $request){

        try {
            $validator = Validator::make($request->all(), [
                'session_token' => 'required',
                'school_id' => 'required',
            ]);

            if ($validator->fails()) {
                $response["Result"] = 0;
                $response["Message"] = implode(',', $validator->errors()->all());
                return response($response, 200);
            }

            else {
                $appuser = User::where("session_token", $request['session_token'])->first();
            }

            if (empty($appuser) || $request['session_token'] == "") {
                $response["Result"] = 0;
                $response["Message"] = "Invalid Session Token";
                return response($response, 200);
            }

            if (!empty($appuser)) {
                if(!empty($request['date'])){
                    $date=Carbon::parse($request['date'])->format('Y-m-d');
                }
                else{
                    $date=Carbon::today()->format('Y-m-d');
                }

                if(!empty($request['class_id']) && !empty($request['division'])) {
                    $attendance_list = Attendance::where('attendance_date', $date)->where('school_id', $request['school_id'])->where('class_name', $request['class_id'])->where('class_division', $request['division'])->where('student_id', '!=', 0)->lists('student_id');

                    $student=Student::where('role','student')->where('school_id', $request['school_id'])->where('class_id', $request['class_id'])
                        ->where('division', $request['division'])->whereNotIn('id',$attendance_list)->get();
                }
                else if(!empty($request['class_id'])){
                    $attendance_list = Attendance::where('attendance_date', $date)->where('school_id', $request['school_id'])->where('class_name', $request['class_id'])->where('student_id', '!=', 0)->lists('student_id');
                    $student=Student::where('role','student')->where('school_id', $request['school_id'])->where('class_id', $request['class_id'])
                        ->whereNotIn('id',$attendance_list)->get();
                }
                else{
                    $attendance_list = Attendance::where('attendance_date', $date)->where('school_id', $request['school_id'])->where('student_id', '!=', 0)->lists('student_id');
                    $student=Student::where('role','student')->where('school_id', $request['school_id'])
                        ->whereNotIn('id',$attendance_list)->get();
                }

                foreach ($student as $key=> $value) {
                    unset($student[$key]['password']);
                    unset($student[$key]['deleted_at']);

                }

                $response["Result"] = 1;
                $response['data']=$student;
                $response["Message"] = "Success";
                return response($response, 200);
            }
        } catch (Exception $e) {
            return response("", 500);
        }
    }

    public function get_all_student_list(Request $request){

        try {
            $validator = Validator::make($request->all(), [
                'session_token' => 'required',
                'school_id' => 'required',
            ]);

            if ($validator->fails()) {
                $response["Result"] = 0;
                $response["Message"] = implode(',', $validator->errors()->all());
                return response($response, 200);
            }
            else {
                $appuser = User::where("session_token", $request['session_token'])->first();
            }
            if (empty($appuser) || $request['session_token'] == "") {
                $response["Result"] = 0;
                $response["Message"] = "Invalid Session Token";
                return response($response, 200);
            }
            if (!empty($appuser)) {
                if(!empty($request['date'])){
                    $date=Carbon::parse($request['date'])->format('Y-m-d');
                }
                else{
                    $date=Carbon::today()->format('Y-m-d');
                }

                if(!empty($request['class_id']) && !empty($request['division'])) {

                    $student=Student::where('role','student')->where('school_id', $request['school_id'])->where('class_id', $request['class_id'])
                        ->where('division', $request['division'])->get();
                }
                else if(!empty($request['class_id'])){
                    $student=Student::where('role','student')->where('school_id', $request['school_id'])->where('class_id', $request['class_id'])
                       ->get();
                }
                else{
                    $student=Student::where('role','student')->where('school_id', $request['school_id'])
                      ->get();
                }

                 $holiday=Calendar::where('school_id',$request['school_id'])->pluck('holiday_date')->toArray();

                foreach ($student as $key=> $value) {

                    if(in_array($date,$holiday)){
                        $student[$key]['attendance'] = 'H';
                    }
                    else{
                    $check_attendance = Attendance::where('attendance_date', $date)->where('student_id', $value->id)->where('staff_id', 0)->first();

                    if (!empty($check_attendance)) {

                        if ($check_attendance['on_leave'] == 1) {
                            $student[$key]['attendance'] = 'L';
                        } else {
                            $student[$key]['attendance'] = 'P';
                        }
                    } else {
                        $student[$key]['attendance'] = 'A';
                    }
                   }
                    unset($student[$key]['password']);
                    unset($student[$key]['deleted_at']);

                }
                $response["Result"] = 1;
                $response['data']=$student;
                $response["Message"] = "Success";
                return response($response, 200);
            }
        } catch (Exception $e) {
            return response("", 500);
        }
    }

    public function get_present_staff_list(Request $request){

        try {
            $validator = Validator::make($request->all(), [
                'session_token' => 'required',
                'school_id' => 'required',
                'staff_role'=>'required',
            ]);

            if ($validator->fails()) {
                $response["Result"] = 0;
                $response["Message"] = implode(',', $validator->errors()->all());
                return response($response, 200);
            }
            else {
                $appuser = User::where("session_token", $request['session_token'])->first();
            }
            if (empty($appuser) || $request['session_token'] == "") {
                $response["Result"] = 0;
                $response["Message"] = "Invalid Session Token";
                return response($response, 200);
            }
            if (!empty($appuser)) {

                if(!empty($request['date'])){
                    $date=Carbon::parse($request['date'])->format('Y-m-d');
                }
                else{
                    $date=Carbon::today()->format('Y-m-d');
                }


                $attendance_list = Attendance::where('attendance_date', $date)->where('school_id', $request['school_id'])
                    ->where('staff_role',$request['staff_role'])->where('staff_id', '!=',0)->lists('staff_id');

                $staff=Staff::where('role','staff')->where('staff_role',$request['staff_role'])->whereIn('id',$attendance_list)->get();

                foreach ($staff as $key=> $value) {
                    unset($staff[$key]['password']);
                    unset($staff[$key]['deleted_at']);

                }

                $response["Result"] = 1;
                $response['data']=$staff;
                $response["Message"] = "Success";
                return response($response, 200);
            }
        } catch (Exception $e) {
            return response("", 500);
        }
    }

    public function get_absent_staff_list(Request $request){

        try {
            $validator = Validator::make($request->all(), [
                'session_token' => 'required',
                'school_id' => 'required',
                'staff_role'=>'required',
            ]);

            if ($validator->fails()) {
                $response["Result"] = 0;
                $response["Message"] = implode(',', $validator->errors()->all());
                return response($response, 200);
            }

            else {
                $appuser = User::where("session_token", $request['session_token'])->first();
            }

            if (empty($appuser) || $request['session_token'] == "") {
                $response["Result"] = 0;
                $response["Message"] = "Invalid Session Token";
                return response($response, 200);
            }

            if (!empty($appuser)) {
                if(!empty($request['date'])){
                    $date=Carbon::parse($request['date'])->format('Y-m-d');
                }
                else{
                    $date=Carbon::today()->format('Y-m-d');
                }

                  $attendance_list = Attendance::where('attendance_date', $date)->where('school_id', $request['school_id'])->where('staff_role',$request['staff_role'])->where('staff_id', '!=', 0)->lists('staff_id');
                  $staff=Staff::where('role','staff')->where('school_id', $request['school_id'])
                        ->whereNotIn('id',$attendance_list)->where('staff_role',$request['staff_role'])->get();

                foreach ($staff as $key=> $value) {
                    unset($staff[$key]['password']);
                    unset($staff[$key]['deleted_at']);

                }
                $response["Result"] = 1;
                $response['data']=$staff;
                $response["Message"] = "Success";
                return response($response, 200);
            }
        } catch (Exception $e) {
            return response("", 500);
        }
    }


    public function get_all_staff_list(Request $request){

        try {
            $validator = Validator::make($request->all(), [
                'session_token' => 'required',
                'school_id' => 'required',
                'staff_role'=>'required',
            ]);

            if ($validator->fails()) {
                $response["Result"] = 0;
                $response["Message"] = implode(',', $validator->errors()->all());
                return response($response, 200);
            }
            else {
                $appuser = User::where("session_token", $request['session_token'])->first();
            }
            if (empty($appuser) || $request['session_token'] == "") {
                $response["Result"] = 0;
                $response["Message"] = "Invalid Session Token";
                return response($response, 200);
            }
            if (!empty($appuser)) {

                if(!empty($request['date'])){
                    $date=Carbon::parse($request['date'])->format('Y-m-d');
                }
                else{
                    $date=Carbon::today()->format('Y-m-d');
                }

                $holiday=Calendar::where('school_id',$request['school_id'])->pluck('holiday_date')->toArray();

                $staff=Staff::where('role','staff')->where('staff_role',$request['staff_role'])->where('school_id', $request['school_id'])->get();

                foreach ($staff as $key=> $value) {

                    if(in_array($date,$holiday)){
                        $staff[$key]['attendance'] = 'H';
                    }
                    else{
                         $check_attendance=Attendance::where('attendance_date',$date)->where('staff_id',$value->id)->where('student_id',0)->first();

                         if (!empty($check_attendance)) {

                            if ($check_attendance['on_leave'] == 1) {
                                $staff[$key]['attendance'] = 'L';

                            }
                            else {
                                $staff[$key]['attendance'] = 'P';
                            }
                         }
                         else {
                            $staff[$key]['attendance'] = 'A';
                         }
                     }

                    unset($staff[$key]['password']);
                    unset($staff[$key]['deleted_at']);
                }
                $response["Result"] = 1;
                $response['data']=$staff;
                $response["Message"] = "Success";
                return response($response, 200);
            }
        } catch (Exception $e) {
            return response("", 500);
        }
    }


    public function forget_password(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                    'email' => 'required|email',
                ]
            );
            if ($validator->fails()) {
                $response["Result"] = 0;
                $response["Message"] = implode(',', $validator->errors()->all());
                return response($response, 200);
            }
            $appuser = User::where("email", $request['email'])->first();
            if (empty($appuser)) {
                $response["Result"] = 0;
                $response["Message"] = "Invalid email address.";
                return response($response, 200);
            }
            else {
                $pass = str_random(8);
                $appuser->password = bcrypt($pass);
                $appuser->save();

                $to1 = $request['email'];
                $from1 = 'admin@school.com';
                $subject2 = 'Forget Password';
                $mailcontent1 = "Dear <b>" . $appuser->name . "</b>, You have requested to reset your password. Please use the password <b>" . $pass . "</b> to log in. After log in, please go to Profile page to change your password.";
                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
                $headers .= "From: $from1\r\n";
                $response["Result"] = 1;
                mail($to1, $subject2, $mailcontent1, $headers);
                $response["Message"] = "New Password Send To Your Mail Account Shortly.";
                return response($response, 200);
            }

        } catch (Exception $e) {
            return response("", 500);
        }
    }

    public function send_notification_sms(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'session_token' => 'required',
                'notification_type' => 'required',
                'notification_to' => 'required',
            ]);

            if ($validator->fails()) {
                $response["Result"] = 0;
                $response["Message"] = implode(',', $validator->errors()->all());
                return response($response, 200);
            }

            $appuser = User::where("session_token", $request['session_token'])->first();
            if (empty($appuser) || $request['session_token'] == "") {
                $response["Result"] = 0;
                $response["Message"] = "Your account may be logged in from other device or deactivated, please try to login again.";
                return response($response, 200);
            }
            if (!empty($appuser)) {

                $sender=$appuser->id;

                if($request['notification_to'] == 'All'){

                    $user_notification=User::where('role','!=','student')->where('school_id',$request['school_id'])->where('id','!=',$sender)->get();
                    $user_sms=User::where('role','!=','student')->where('school_id',$request['school_id'])->where('id','!=',$sender)->get();

                    $present = 'You are  present in  school on '.$request['date'];
                    $absent = 'You are remain absent on '.$request['date'];
                    $holiday = 'There is holiday in our school on  '.$request['date'];
                    $emergency_close = 'School will be closed on '.$request['date'].' , All students and are not expected to report to school , All after school activities have been cancelled.';
                    $vacation = 'School is remain close till '.$request['date'].' because of vacation. New semester will be started from '.Carbon::parse($request['date'] )->modify('+1 day')->format('Y-m-d');
                }

                else if($request['notification_to'] == 'Principal'){

                    $user_notification=User::where('role','staff')->where('staff_role','principal')->where('school_id',$request['school_id'])->where('id','!=',$sender)->get();
                    $user_sms=User::where('role','staff')->where('staff_role','principal')->where('school_id',$request['school_id'])->where('id','!=',$sender)->get();

                    $present = 'Dear Principal , You are  present in  school on '.$request['date'];
                    $absent = 'Dear Principal , You remain absent on 23 '.$request['date'];
                    $holiday = 'Dear Principal , there is holiday in our school on '. $request['date'];
                    $emergency_close = 'Dear Principal , school will be closed on '.$request['date'].', All students and are not expected to report to school , All after school activities have been cancelled.';
                    $vacation = 'Dear Principal school is remain close '.$request['date'].' because of vacation. New semester will be started from '.Carbon::parse($request['date'] )->modify('+1 day')->format('Y-m-d');

                }
                else if($request['notification_to'] == 'Teacher'){

                    $user_notification=User::where('role','staff')->where('staff_role','teacher')->where('school_id',$request['school_id'])->where('id','!=',$sender)->get();
                    $user_sms=User::where('role','staff')->where('staff_role','teacher')->where('school_id',$request['school_id'])->where('id','!=',$sender)->get();

                    $present = 'Dear Teacher , You has come to school on '.$request['date'];
                    $absent = 'Dear Teacher , You remain absent on '.$request['date'];
                    $holiday = 'Dear Teacher , there is holiday in our school on '.$request['date'];
                    $emergency_close = 'Dear Teacher , school will be closed on '.$request['date'].' , All students and are not expected to report to school , All after school activities have been cancelled.';
                    $vacation = 'Dear Teacher school is remain close tll '.$request['date'].' because of vacation. New semester will be started from '.Carbon::parse($request['date'] )->modify('+1 day')->format('Y-m-d');
                }

                else if($request['notification_to'] == 'Parents'){
                    $user_notification = new \Illuminate\Database\Eloquent\Collection;
                    $user_sms = new \Illuminate\Database\Eloquent\Collection;

                    if($request['student_id'] != ''){
                        $user_notification = ParentChild::select('parent_id')->where('student_id',$request['student_id'])->get();
                        foreach($user_notification as $value) {
                            $parent=User::where('id',$value->parent_id)->first();
                            if(!empty($parent)){
                                $user_sms->add($parent);
                            }
                        }
                    }

                    else if ($request['school_id'] != '' && $request['class_name'] != '' && $request['class_division'] != '') {
                        $student = User::select('id')->where('role','student')->where('school_id', $request['school_id'])->where('class_id', $request['class_name'])->where('division', $request['class_division'])->get();
                        foreach($student as $value) {
                            $parent_child=ParentChild::select('parent_id')->where('student_id',$value->id)->first();
                            if(!empty($parent_child)) {
                                $user_notification->add($parent_child);
                            }
                            $parent=User::where('id',$parent_child->parent_id)->first();
                            if(!empty($parent)){
                                $user_sms->add($parent);
                            }
                        }
                    } else if ($request['school_id'] != '' && $request['class_name'] != '') {
                        $student =  User::select('id')->where('role','student')->where('school_id', $request['school_id'])->where('class_id', $request['class_name'])->get();
                        foreach($student as $value) {
                            $parent_child=ParentChild::select('parent_id')->where('student_id',$value->id)->first();
                            if(!empty($parent_child)) {
                                $user_notification->add($parent_child);
                            }
                            $parent=User::where('id',$parent_child->parent_id)->first();
                            if(!empty($parent)){
                                $user_sms->add($parent);
                            }
                        }
                    }
                    else if ($request['school_id'] != '') {
                        $student = User::select('id')->where('role','student')->where('school_id', $request['school_id'])->get();
                        foreach($student as $value) {
                            $parent_child=ParentChild::select('parent_id')->where('student_id',$value->id)->first();
                            if(!empty($parent_child)) {
                                $user_notification->add($parent_child);

                                $parent=User::where('id',$parent_child->parent_id)->first();
                                if(!empty($parent)){
                                    $user_sms->add($parent);
                                }
                            }
                        }
                    }
                    else {
                    }
                    $present = 'Dear Parents , Your Child  has come to school on '.$request['date'];
                    $absent = 'Dear Parents , Your Child  remain absent on ' .$request['date'];
                    $holiday = 'Dear Parents , there is holiday in our school on '.$request['date'];
                    $emergency_close = 'Dear Parents , school will be closed on '.$request['date'].' , All students and are not expected to report to school , All after school activities have been cancelled.';
                    $vacation = 'Dear Parents school is remain close till '.$request['date'].' because of vacation. New semester will be started from '.Carbon::parse($request['date'] )->modify('+1 day')->format('Y-m-d');;
                }
                else{
                    $user_notification = new \Illuminate\Database\Eloquent\Collection;
                    $user_sms = new \Illuminate\Database\Eloquent\Collection;
                    $present = '';
                    $absent = '';
                    $holiday = '';
                    $emergency_close = '';
                    $vacation = '';
                }
               
                if(!empty($request['notification_type'])){
                    $noti_type=explode(',',$request['notification_type']);
                    foreach($noti_type as $type){

                        if($type == 'sms'){

                            if(empty($request['sms_type'])){
                                $response["Result"] = 0;
                                $response["Message"] = 'sms_type field required!';
                                return response($response, 200);
                            }

                            $final_list='';
                            $msg='';
                            $list='';

                            if($request['sms_type'] == 'promotional' ){

                                if(empty($request['message'])){
                                    $response["Result"] = 0;
                                    $response["Message"] = 'Message field required!';
                                    return response($response, 200);
                                }
                                $msg=$request['message'];
                                $sid='WEBSMS';
                                $msg_type='';
                            }

                            if($request['sms_type'] == 'transactional' ){

                                if(empty($request['transactional_type'])){
                                    $response["Result"] = 0;
                                    $response["Message"] = 'Transactional_type field required!';
                                    return response($response, 200);
                                }

                                if(empty($request['date'])){
                                    $response["Result"] = 0;
                                    $response["Message"] = 'date field required!';
                                    return response($response, 200);
                                }

                                $sid='ALAIPL';
                                $msg_type='&gwid=2';

                                if($request['transactional_type'] == 'present'){
                                    $msg=$present;
                                }
                                if($request['transactional_type'] == 'absent'){
                                    $msg=$absent;
                                }
                                if($request['transactional_type'] == 'emergency_close'){
                                    $msg=$emergency_close;
                                }

                                if($request['transactional_type'] == 'holiday'){
                                    $msg=$holiday;
                                }
                                    if($request['transactional_type'] == 'vacation'){
                                    $msg=$vacation;
                                }
                            }

                            foreach($user_sms as $key11=> $value11){
                                if(isset($value11->mobile) &&  strlen($value11->mobile) == 10) {
                                    $list.=$value11->mobile.',';
                                    $receiver = $value11->id;
                                    $input['type'] = $type;
                                    $input['receiver_id'] = $receiver;
                                    $input['sender_id'] = $sender;
                                    $input['message'] = $msg;
                                    Notification::create($input);
                                }
                                $final_list=trim($list,',');
                            }
                            $ch = curl_init();// init curl
                            curl_setopt($ch, CURLOPT_URL,"http://bulksms.alayada.com/vendorsms/pushsms.aspx");
                            curl_setopt($ch, CURLOPT_POST, 1);// set post data to true
                            curl_setopt($ch, CURLOPT_POSTFIELDS,"user=chetanjsheth&password=C123123H1212&msisdn=".$final_list."&sid=".$sid."&msg=".$msg."&fl=0".$msg_type."");// post data
                            // receive server response ...
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);// gives you a response from the server

                            $respon = curl_exec ($ch);// response it ouputed in the response var
                            curl_close ($ch);
                        }

                        if($type == 'notification'){

                            if(empty($request['message'])){
                                $response["Result"] = 0;
                                $response["Message"] = 'Message field required!';
                                return response($response, 200);
                            }

                            foreach($user_notification as $key=> $value){
                                if($request['notification_to'] == 'Parents'){
                                    $receiver=$value->parent_id;
                                    $get_user=User::where('id',$receiver)->first();

                                    $user_notification[$key]['deviceToken']=$get_user['deviceToken'];
                                    $user_notification[$key]['device_type']=$get_user['device_type'];
                                }
                                else {
                                    $receiver = $value->id;
                                }
                                $input['type']=$type;
                                $input['receiver_id']=$receiver;
                                $input['sender_id']=$sender;
                                $input['message']=$request['message'];

                                /* ANDROID PUSH NOTIFICATION */
                                if($value['device_type'] == 'android') {
                                    $device_char = strlen($value['deviceToken']);
                                    if ($value['deviceToken'] != "" && $device_char >= 20) {
                                        PushNotification::setService('fcm')
                                            ->setMessage([
                                                'notification' => [
                                                    'title' => $appuser->name. ' message from School',
                                                    'body' => $input['message'],
                                                    'sound' => 'default'
                                                ],
                                                'data' => [
                                                    'extraPayLoad1' => 'value1',
                                                    'extraPayLoad2' => 'value2'
                                                ]
                                            ])
                                           // ->setApiKey('AAAANfHw2Ls:APA91bEvYnZf_EHIqdrjyW-aPN-XpTZXt6bHeigfJATId9IKDCZqh6hLppD5CvN31yYCQPYzh5SYOK4ZscFF7rI7oR1TeS2w5cBpGZB0eGCWOsnw1MyyTx4os4F3jdKo_4opnXZrWxIY')
                                            // ->setDevicesToken(['feyVriUPVz8:APA91bECPaymlADpggozluJEOx0j2WZ8ptCoj1eLwjOdu-R4xVaJs56aUqmL8FXiA1qlrgJAjyj4JDsU9TrodSm4DCKB7lWeFNcmiwhPFfJdfmyNRcqWwsdPaaYg61s23OGTAMhk3p01'])
                                            ->setDevicesToken([$value['deviceToken']])
                                            ->send()
                                            ->getFeedback();
                                    }
                                }
                                Notification::create($input);
                            }
                        }
                    }
                }

                $response["Result"] = 1;
                $response["Message"] = "Notification sent Successfully";
                return response($response, 200);
            }
        } catch (Exception $e) {
            return response("", 500);
        }
    }

    public function make_student_present_leave(Request $request){

        try {
            $validator = Validator::make($request->all(), [
                    'session_token'=>'required',
                    'student_id' => 'required',
                    'attendance_date' => 'required',
                ]
            );

            if ($validator->fails()) {
                $response["Result"] = 0;
                $response["Message"] = implode(',', $validator->errors()->all());
                return response($response, 200);
            }
            $appuser = User::where("session_token", $request['session_token'])->first();
            if (empty($appuser) || $request['session_token'] == "") {
                $response["Result"] = 0;
                $response["Message"] = "Your account may be logged in from other device or deactivated, please try to login again.";
                return response($response, 200);
            }
            else {

                $student=Student::where('role','student')->where('id',$request['student_id'])->first();
                if(empty($student)){
                    $response["Result"] = 0;
                    $response["Message"] = "Student not found";
                    return response($response, 200);
                }

                $input=$request->all();

                $input['status']='active';
                $input['school_id']=$student['school_id'];
                $input['class_name']=$student['class_id'];
                $input['class_division']=$student['division'];
                $input['attendance_time']=\Carbon\Carbon::now()->format('H:i:s');

                $device = Device::where('school_id', $request['school_id'])->where('device_type', 'main gate')->first();

                if (!empty($device)) {
                    $device_id = $device->id;
                } else {
                    $device_id = 0;
                }

                $input['device_id'] = $device_id;
                if (!empty($request['on_leave'])) {
                    $input['on_leave'] = $request['on_leave'];
                    $input['school_in_time'] = '';
                    $input['school_out_time'] = '';
                } else {
                    $input['on_leave'] = 0;
                }

                $input['staff_role'] = '';

                Attendance::create($input);

                /*send notification or sms if any remark is added*/

                if (!empty($request['remark']) && $request['remark'] != '') {
                    $input1['sender_id'] = $appuser->id;
                    $parent = ParentChild::where('student_id', $request['student_id'])->first();
                    if (!empty($parent)) {
                        $parent_id = $parent->parent_id;
                    } else {
                        $parent_id = 0;
                    }
                    $input1['receiver_id'] = $parent_id;
                    $input1['message'] = $request['remark'];

                    $user = User::where('id', $parent_id)->first();
                    if (!empty($user)) {
                        if ($request['notification_type'] == 'both') {

                            if ($user['device_type'] == 'android') {
                                $device_char = strlen($user['deviceToken']);
                                if ($user['deviceToken'] != "" && $device_char >= 20) {
                                    \Edujugon\PushNotification\Facades\PushNotification::setService('fcm')
                                        ->setMessage([
                                            'notification' => [
                                                'title' => $appuser->name . ' message from School',
                                                'body' => $input1['message'],
                                                'sound' => 'default'
                                            ],
                                            'data' => [
                                                'extraPayLoad1' => 'value1',
                                                'extraPayLoad2' => 'value2'
                                            ]
                                        ])
                                        ->setApiKey('AAAANfHw2Ls:APA91bEvYnZf_EHIqdrjyW-aPN-XpTZXt6bHeigfJATId9IKDCZqh6hLppD5CvN31yYCQPYzh5SYOK4ZscFF7rI7oR1TeS2w5cBpGZB0eGCWOsnw1MyyTx4os4F3jdKo_4opnXZrWxIY')
                                        ->setDevicesToken([$user['deviceToken']])
                                        ->send()
                                        ->getFeedback();
                                }
                            }

                            $ch = curl_init();// init curl
                            curl_setopt($ch, CURLOPT_URL, "http://bulksms.alayada.com/vendorsms/pushsms.aspx");
                            curl_setopt($ch, CURLOPT_POST, 1);// set post data to true
                            curl_setopt($ch, CURLOPT_POSTFIELDS, "user=chetanjsheth&password=C123123H1212&msisdn=" . $user['mobile'] . "&sid=WEBSMS&msg=" . $input1['message'] . "&fl=0");// post data
                            // receive server response ...
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);// gives you a response from the server
                            $responce = curl_exec($ch); // response it ouputed in the response var
                            curl_close($ch);
                        }

                        if ($request['notification_type'] == 'sms') {
                            $ch = curl_init();// init curl
                            curl_setopt($ch, CURLOPT_URL, "http://bulksms.alayada.com/vendorsms/pushsms.aspx");
                            curl_setopt($ch, CURLOPT_POST, 1);// set post data to true
                            curl_setopt($ch, CURLOPT_POSTFIELDS, "user=chetanjsheth&password=C123123H1212&msisdn=" . $user['mobile'] . "&sid=WEBSMS&msg=" . $input1['message'] . "&fl=0");// post data
                            // receive server response ...
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // gives you a response from the server
                            $responce = curl_exec($ch);// response it ouputed in the response var
                            curl_close($ch);
                        }

                        if ($request['notification_type'] == 'notification') {
                            /* ANDROID PUSH NOTIFICATION */
                            if ($user['device_type'] == 'android') {
                                $device_char = strlen($user['deviceToken']);
                                if ($user['deviceToken'] != "" && $device_char >= 20) {
                                    \Edujugon\PushNotification\Facades\PushNotification::setService('fcm')
                                        ->setMessage([
                                            'notification' => [
                                                'title' => $appuser->name . ' message from School',
                                                'body' => $input['message'],
                                                'sound' => 'default'
                                            ],
                                            'data' => [
                                                'extraPayLoad1' => 'value1',
                                                'extraPayLoad2' => 'value2'
                                            ]
                                        ])
                                        ->setApiKey('AAAANfHw2Ls:APA91bEvYnZf_EHIqdrjyW-aPN-XpTZXt6bHeigfJATId9IKDCZqh6hLppD5CvN31yYCQPYzh5SYOK4ZscFF7rI7oR1TeS2w5cBpGZB0eGCWOsnw1MyyTx4os4F3jdKo_4opnXZrWxIY')
                                        ->setDevicesToken([$user['deviceToken']])
                                        ->send()
                                        ->getFeedback();
                                }
                            }
                        }

                        Notification::create($input1);
                    }
                }
                $response["Result"] = 1;
                $response["Message"] = "Attendance added successfully.";
                return response($response, 200);
            }

        } catch (Exception $e) {
            return response("", 500);
        }
    }

    public function make_student_absent(Request $request){

        try {
            $validator = Validator::make($request->all(), [
                    'session_token'=>'required',
                    'student_id' => 'required',
                    'attendance_date' => 'required',
                ]
            );

            if ($validator->fails()) {
                $response["Result"] = 0;
                $response["Message"] = implode(',', $validator->errors()->all());
                return response($response, 200);
            }

            $appuser = User::where("session_token", $request['session_token'])->first();

            if (empty($appuser) || $request['session_token'] == "") {
                $response["Result"] = 0;
                $response["Message"] = "Your account may be logged in from other device or deactivated, please try to login again.";
                return response($response, 200);
            }
            else {

                $Check_attendance=Attendance::where('student_id',$request['student_id'])->where('attendance_date',$request['attendance_date'])->first();
                  if(empty($Check_attendance)){
                      $response["Result"] = 0;
                      $response["Message"] = "No record found.";
                      return response($response, 200);
                  }
                 else{
                     $check_attendancedetail=AttendanceDetail::where('attendance_id',$Check_attendance->id)->delete();
                     $Check_attendance->delete();

                     $response["Result"] = 1;
                     $response["Message"] = "Student is absent now removed from present list.";
                     return response($response, 200);
                   }
                 }

        } catch (Exception $e) {
            return response("", 500);
        }
    }

    public function make_staff_present_leave(Request $request){

        try {
            $validator = Validator::make($request->all(), [
                    'session_token'=>'required',
                    'staff_id' => 'required',
                    'attendance_date' => 'required',
                ]
            );

            if ($validator->fails()) {
                $response["Result"] = 0;
                $response["Message"] = implode(',', $validator->errors()->all());
                return response($response, 200);
            }
            $appuser = User::where("session_token", $request['session_token'])->first();
            if (empty($appuser) || $request['session_token'] == "") {
                $response["Result"] = 0;
                $response["Message"] = "Your account may be logged in from other device or deactivated, please try to login again.";
                return response($response, 200);
            }
            else {

                $staff=Staff::where('role','staff')->where('id',$request['staff_id'])->first();

                if(empty($staff)){
                    $response["Result"] = 0;
                    $response["Message"] = "Staff not found";
                    return response($response, 200);
                }

                $input=$request->all();

                $input['status']='active';
                $input['school_id']=$staff['school_id'];
                $input['class_name']='';
                $input['class_division']='';
                $input['attendance_time']=\Carbon\Carbon::now()->format('H:i:s');

                $device = Device::where('school_id', $request['school_id'])->where('device_type', 'main gate')->first();

                if (!empty($device)) {
                    $device_id = $device->id;
                } else {
                    $device_id = 0;
                }

                $input['device_id'] = $device_id;

                if (!empty($request['on_leave'])) {
                    $input['on_leave'] = $request['on_leave'];
                    $input['school_in_time'] = '';
                    $input['school_out_time'] = '';
                } else {
                    $input['on_leave'] = 0;
                }

                $input['staff_role'] = $staff['staff_role'];

                Attendance::create($input);

                /*send notification or sms if any remark is added*/

                if(!empty($request['remark']) && $request['remark'] != '') {
                    if (!empty($staff)) {

                        $input1['sender_id'] = $appuser->id;
                        $input1['receiver_id'] = $staff->id;
                        $input1['message'] = $request['remark'];
                        $input1['notification_type']=$request['notification_type'];

                        if ($request['notification_type'] == 'both') {

                            if ($staff['device_type'] == 'android') {
                                $device_char = strlen($staff['deviceToken']);
                                if ($staff['deviceToken'] != "" && $device_char >= 20) {
                                    \Edujugon\PushNotification\Facades\PushNotification::setService('fcm')
                                        ->setMessage([
                                            'notification' => [
                                                'title' => $appuser->name . ' message from School',
                                                'body' => $input1['message'],
                                                'sound' => 'default'
                                            ],
                                            'data' => [
                                                'extraPayLoad1' => 'value1',
                                                'extraPayLoad2' => 'value2'
                                            ]
                                        ])
                                        ->setApiKey('AAAANfHw2Ls:APA91bEvYnZf_EHIqdrjyW-aPN-XpTZXt6bHeigfJATId9IKDCZqh6hLppD5CvN31yYCQPYzh5SYOK4ZscFF7rI7oR1TeS2w5cBpGZB0eGCWOsnw1MyyTx4os4F3jdKo_4opnXZrWxIY')
                                        ->setDevicesToken([$staff['deviceToken']])
                                        ->send()
                                        ->getFeedback();
                                }
                            }

                            $ch = curl_init();// init curl
                            curl_setopt($ch, CURLOPT_URL, "http://bulksms.alayada.com/vendorsms/pushsms.aspx");
                            curl_setopt($ch, CURLOPT_POST, 1);// set post data to true
                            curl_setopt($ch, CURLOPT_POSTFIELDS, "user=chetanjsheth&password=C123123H1212&msisdn=" . $staff['mobile'] . "&sid=WEBSMS&msg=" . $input1['message'] . "&fl=0");// post data
                            // receive server response ...
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);// gives you a response from the server
                            $responce = curl_exec($ch);// response it ouputed in the response var
                            curl_close($ch);
                        }

                        if ($request['notification_type'] == 'sms') {

                            $ch = curl_init();// init curl
                            curl_setopt($ch, CURLOPT_URL, "http://bulksms.alayada.com/vendorsms/pushsms.aspx");
                            curl_setopt($ch, CURLOPT_POST, 1);// set post data to true
                            curl_setopt($ch, CURLOPT_POSTFIELDS, "user=chetanjsheth&password=C123123H1212&msisdn=" . $staff['mobile'] . "&sid=WEBSMS&msg=" . $input1['message'] . "&fl=0");// post data
                            // receive server response ...
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);// gives you a response from the server
                            $responce = curl_exec($ch);// response it ouputed in the response var
                            curl_close($ch);
                        }

                        if ($request['notification_type'] == 'notification') {
                            /* ANDROID PUSH NOTIFICATION */
                            if ($staff['device_type'] == 'android') {
                                $device_char = strlen($staff['deviceToken']);
                                if ($staff['deviceToken'] != "" && $device_char >= 20) {
                                    \Edujugon\PushNotification\Facades\PushNotification::setService('fcm')
                                        ->setMessage([
                                            'notification' => [
                                                'title' => $appuser->name . ' message from School',
                                                'body' => $input1['message'],
                                                'sound' => 'default'
                                            ],
                                            'data' => [
                                                'extraPayLoad1' => 'value1',
                                                'extraPayLoad2' => 'value2'
                                            ]
                                        ])
                                        ->setApiKey('AAAANfHw2Ls:APA91bEvYnZf_EHIqdrjyW-aPN-XpTZXt6bHeigfJATId9IKDCZqh6hLppD5CvN31yYCQPYzh5SYOK4ZscFF7rI7oR1TeS2w5cBpGZB0eGCWOsnw1MyyTx4os4F3jdKo_4opnXZrWxIY')
                                        ->setDevicesToken([$staff['deviceToken']])
                                        ->send()
                                        ->getFeedback();
                                }
                            }
                        }
                        Notification::create($input1);
                    }
                }

                $response["Result"] = 1;
                $response["Message"] = "Attendance added successfully.";
                return response($response, 200);
            }

        } catch (Exception $e) {
            return response("", 500);
        }
    }

    public function make_staff_absent(Request $request){

        try {
            $validator = Validator::make($request->all(), [
                    'session_token'=>'required',
                    'staff_id' => 'required',
                    'attendance_date' => 'required',
                ]
            );

            if ($validator->fails()) {
                $response["Result"] = 0;
                $response["Message"] = implode(',', $validator->errors()->all());
                return response($response, 200);
            }

            $appuser = User::where("session_token", $request['session_token'])->first();
            if (empty($appuser) || $request['session_token'] == "") {
                $response["Result"] = 0;
                $response["Message"] = "Your account may be logged in from other device or deactivated, please try to login again.";
                return response($response, 200);
            }
            else {

                $Check_attendance=Attendance::where('staff_id',$request['staff_id'])->where('attendance_date',$request['attendance_date'])->first();
                if(empty($Check_attendance)){
                    $response["Result"] = 0;
                    $response["Message"] = "No record found.";
                    return response($response, 200);
                }
                else{
                    $check_attendancedetail=AttendanceDetail::where('attendance_id',$Check_attendance->id)->delete();
                    $Check_attendance->delete();

                    $response["Result"] = 1;
                    $response["Message"] = "Staff is absent now removed from present list.";
                    return response($response, 200);
                }
            }

        } catch (Exception $e) {
            return response("", 500);
        }
    }

     public function test_notification(Request $request){

//                $a= \Davibennun\LaravelPushNotification\Facades\PushNotification::app('appNameAndroid')
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
                   // ->setApiKey('AAAANfHw2Ls:APA91bEvYnZf_EHIqdrjyW-aPN-XpTZXt6bHeigfJATId9IKDCZqh6hLppD5CvN31yYCQPYzh5SYOK4ZscFF7rI7oR1TeS2w5cBpGZB0eGCWOsnw1MyyTx4os4F3jdKo_4opnXZrWxIY')
                     ->setDevicesToken(['eP953ZpfUA8:APA91bGuwef1Vm3F67AItYjcTcG42hgMczrcyxUC8k7nQLV6DeUD54HZmxAEyyUmTgBPrlCtf0Ds6spFAFhhUwZ5hrbajGTMVE3nLyTP2UmaQlStTZoKtgw-nc1IgMpViEhgHcsUAS00'])
                        ->send()
                    ->getFeedback();
                    print_r($a);
//                $push = new PushNotification('fcm');
//                $push->setMessage(['body'=>'This is the message','title'=>'This is the title'])
//                    ->setApiKey('AAAANfHw2Ls:APA91bEvYnZf_EHIqdrjyW-aPN-XpTZXt6bHeigfJATId9IKDCZqh6hLppD5CvN31yYCQPYzh5SYOK4ZscFF7rI7oR1TeS2w5cBpGZB0eGCWOsnw1MyyTx4os4F3jdKo_4opnXZrWxIY')
//                    ->setDevicesToken(['feyVriUPVz8:APA91bECPaymlADpggozluJEOx0j2WZ8ptCoj1eLwjOdu-R4xVaJs56aUqmL8FXiA1qlrgJAjyj4JDsU9TrodSm4DCKB7lWeFNcmiwhPFfJdfmyNRcqWwsdPaaYg61s23OGTAMhk3p01'])
//                    ->send()
//                 ->getFeedback();
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

         curl_setopt($ch, CURLOPT_POSTFIELDS,"user=chetanjsheth&password=C123123H1212&msisdn=9773203112&sid=WEBSMS&msg=Dear Parents , Your Child Chirag janjmera has come to school at 9:30am 23 March 2017.&fl=0");// post data

            // receive server response ...
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);// gives you a response from the server

            $response = curl_exec ($ch);// response it ouputed in the response var

         curl_close ($ch);// close curl connection
        return $response;
    }
}