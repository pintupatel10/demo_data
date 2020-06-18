<?php

namespace App\Http\Controllers\api;
use App\Attendance;
use App\AttendanceDetail;
use App\Device;
use App\ParentChild;
use App\Parents;
use App\School;
use App\Staff;
use App\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Encryption\DecryptException;
use Validator;
use File;

class StaffController extends Controller
{

    public $to = "";
    public function login(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                    'email' => 'required',
                    'password' => 'required',
                    'device_type' => 'required',
                ]
            );
            if ($validator->fails()){
                $response["status"] = "400";
                $response["message"] = implode(',', $validator->errors()->all());
                return response($response,400);
            }

            if (Auth::attempt(array('email' => $request['email'], 'password' => $request['password']))) {

                $user = Auth::user();

                if($user->status == 'inactive') {
                    $response["status"] = "403";
                    $response["message"] = "Your account is deactivated, please contact administrator for further information.";;
                    return response($response,403);
                }

                if($request['push_token']!=""){ $user['push_token'] = $request['push_token']; }
                if($request['push_type']!=""){ $user['push_type'] = $request['push_type']; }
                $user['session_token'] = str_random(30);
                $user->save();

                if($user->role == 'staff') {

                    $school_det = School::findorFail($user->school_id);
                    $school_name = $school_det->name;
                    $roles = $user->role .'-'. $user->staff_role;

                } else if ($user->role == 'admin') {
                    $school_name = 'Schools';
                    $roles = $user->role;
                } else if ($user->role == 'parent') {
                    $school_name = 'Child';
                    $roles = $user->role;
                } else {
                    $school_name = 'Other';
                    $roles = $user->role;
                }

                $return_arr = array(
                    'user_id'=>$user->id,
                    'session_token'=>$user->session_token,
                    'display_name'=>$user->name,
                    'role'=>$roles,
                    'mobile'=>$user->mobile,
                    'school_id'=>$user->school_id,
                    'page_title'=>$school_name,
                    'email'=>$user->email,

                );

                return $return_arr;

            } else {
                $response["status"] = "403";
                $response["message"] = "Email address or password is incorrect. Please try again.";
                return response($response,403);
            }
        }
        catch(Exception $e){
            return response("", 500);
        }
    }

    public function getdata(Request $request)
    {
        try{

            $user = User::where("session_token",$request['session_token'])->first();

            if(empty($user) || $request['session_token']==""){
                $response["status"] = "401";
                $response["message"] = "Your account may be logged in from other device or deactivated, please try to login again.";
                return response($response,401);
            }

            $validator = Validator::make($request->all(), [
                    'session_token' => 'required',
                    'keyword' => 'required',
                ]
            );

            if ($validator->fails()){
                return response("Required parameter missing",400);
            }

            $keyword = $request['keyword'];

            if($user->role == 'admin') {

                if($keyword == 'schools') {

                    $data = [];

                    $schools = School::all();

                    $s = 0;
                    $today = Carbon::today()->toDateString();

                    foreach($schools as $school) {
                        $data['InfoData'][$s]['MainTitle'] = $school->name;
                       // $data['InfoData'][$s]['school_id'] = $school->id;

                        $parents = User::select('id')->where('role','parent')->where('school_id', $school->id)->get();
                        $students = User::select('id')->where('role','student')->where('school_id', $school->id)->get();
                        $staffs = User::select('id')->where('role','staff')->where('school_id', $school->id)->get();

                        $scount = count($students);
                        $pcount = count($parents);
                        $tcount = count($staffs);
                        $sp_count = 0;
                        $tp_count = 0;

                        if($scount > 0) {
                            $spresent = Attendance::select('id')->where('attendance_date', $today)->whereIn('student_id', $students)->get()->all();
                            $sp_count = count($spresent);
                        }

                        if($tcount > 0) {
                            $tpresent = Attendance::select('id')->where('attendance_date', $today)->whereIn('staff_id', $staffs)->get()->all();
                            $tp_count = count($tpresent);
                        }

                        $data['InfoData'][$s]['SubTitle'] = array('parents' => $pcount, 'students' => $scount, 'staff' => $tcount, 'students_present' => $sp_count, 'students_absent' => ($scount-$sp_count), 'staff_present' => $tp_count, 'staff_absent' => ($tcount - $tp_count));
                        $s++;

                    }

                    return $data;

                }

                if($keyword == 'school') {

                    $school_id = $request['school_id'];

                    if(intval($school_id) <= 0) {
                        $response["status"] = "401";
                        $response["message"] = "School not provided.";
                        return response($response,401);
                    }

                    $data = [];

                    $s = 0;
                    $today = Carbon::today()->toDateString();

                    $schools = School::where('id',$school_id)->get();

                    if(empty($schools)) {
                        $response["status"] = "401";
                        $response["message"] = "School not found.";
                        return response($response,401);
                    }

                    foreach($schools as $school) {
                        $data['InfoData'][$s]['MainTitle'] = $school->name;

                        $parents = User::select('id')->where('role','parent')->where('school_id', $school->id)->get();
                        $students = User::select('id')->where('role','student')->where('school_id', $school->id)->get();
                        $staffs = User::select('id')->where('role','staff')->where('school_id', $school->id)->get();

                        $scount = count($students);
                        $pcount = count($parents);
                        $tcount = count($staffs);
                        $sp_count = 0;
                        $tp_count = 0;

                        if($scount > 0) {
                            $spresent = Attendance::select('id')->where('attendance_date', $today)->whereIn('student_id', $students)->get()->all();
                            $sp_count = count($spresent);
                        }

                        if($tcount > 0) {
                            $tpresent = Attendance::select('id')->where('attendance_date', $today)->whereIn('staff_id', $staffs)->get()->all();
                            $tp_count = count($tpresent);
                        }

                        $data['InfoData'][$s]['SubTitle'] = array('parents' => $pcount, 'students' => $scount, 'staff' => $tcount, 'students_present' => $sp_count, 'students_absent' => ($scount-$sp_count), 'staff_present' => $tp_count, 'staff_absent' => ($tcount - $tp_count));
                        $s++;
                        return $data;

                    }

                    return $data;

                }
            } else if ($user->role == 'parent') {

                if($keyword == 'parent') {

                    $data = [];

                    $from_dt = $request['fromDT'];
                    $to_dt   = $request['toDT'];

                    if($from_dt == '') {
                        $from_dt = Carbon::today()->toDateString();
                    } else {
                        $from_dt = Carbon::parse($from_dt);
                    }

                    if($to_dt == '') {
                        $to_dt = Carbon::today()->toDateString();
                    } else {
                        $to_dt = Carbon::parse($to_dt);
                    }

                    $dates = array($from_dt->toDateString(), $to_dt->toDateString());

                    $students = ParentChild::select('student_id')->where('parent_id', $user->id)->get();

                    $total_days = $to_dt->diffInDays($from_dt);

                    $initial = $from_dt;

                    $working_dates = [];

                    while($initial != $to_dt) {
                        $working_dates[] = $initial->toDateString();
                        $initial->modify('+1 day');
                    }

                    $s = 0;
                    foreach($students as $student) {

                        $stud = Student::findorfail($student->student_id);
                        $spresent = Attendance::whereBetween('attendance_date', $dates)->where('student_id', $student->student_id)->get(); //

                        $data['InfoData'][$s]['MainTitle'] = $stud->name;
                        $data['InfoData'][$s]['SubTitle']['total'] = $total_days;
                        $data['InfoData'][$s]['SubTitle']['present'] = count($spresent);
                        $data['InfoData'][$s]['SubTitle']['absent'] = ($total_days - count($spresent));

                        $present_days = [];
                        foreach($spresent as $p) {
                            $present_days[] = $p->attendance_date;
                        }

                        foreach($working_dates as $work) {
                            $present = 0;
                            if(in_array($work, $present_days)) {
                                $present = 1;
                            }

                            $data['InfoData'][$s]['SubTitle']['details'][] = array('date' => $work, 'isPresent' => $present);

                        }
                        $s++;
                    }
                    return $data;
                }
            }
        }
        catch(Exception $e){
            return response("", 500);
        }
    }

    public function forgot_password(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                    'email' => 'required|email',
                ]
            );
            if ($validator->fails()){
                return response("",400);
            }

            $user = Staff::where("email",$request['email'])->first();
            if(empty($user)){
                $response["status"] = "400";
                $response["message"] = "Email address invalid. Please try again.";
                return response($response,400);
            }
            else {
                $data = array('name'=>"Cartier admin");
                $this->to = $request['email'];
                Mail::send('email.forgetpassword', $data, function ($message) {
                    $message->from('noreply@test.com', 'Forget Password');
                    $message->to($this->to)->subject('Forget Password');
                });
            }
        }
        catch(Exception $e){
            return response("", 500);
        }
    }


    public function update_push_token(Request $request)
    {
        try{
            $user = Staff::where("session_token",$request['session_token'])->first();
            if(empty($user) || $request['session_token']==""){
                $response["status"] = "401";
                $response["message"] = "Your account may be logged in from other device or deactivated, please try to login again.";
                return response($response,401);
            }
            $validator = Validator::make($request->all(), [
                    'session_token' => 'required',
                    'push_token' => 'required',
                    'push_type' => 'required',
                    'language' => 'required',
                ]
            );
            if ($validator->fails()){
                return response("",400);
            }

            if(!empty($user))
            {
                if($request['push_token']!=""){ $user['push_token'] = $request['push_token']; }
                if($request['push_type']!=""){ $user['push_type'] = $request['push_type']; }
                if($request['language']!=""){ $user['language'] = $request['language']; }
                $user->save();
            }
        }
        catch(Exception $e){
            return response("", 500);
        }
    }

    public function set_user_account(Request $request)
    {
        try{

            $user = Staff::where("session_token",$request['session_token'])->first();
            if(empty($user) || $request['session_token']==""){
                $response["status"] = "401";
                $response["message"] = "Your account may be logged in from other device or deactivated, please try to login again.";
                return response($response,401);
                //return response("Your account may be logged in from other device or deactivated, please try to login again.",401);
            }

            $validator = Validator::make($request->all(), [
                'display_name' => 'required',
                'password' => 'required | min:8 |regex:/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*()+]+.*)[0-9a-zA-Z\w!@#$%^&*()+]{8,}$/',
                'session_token' => 'required',
                'language' => 'required',
            ],['password.*'=> 'Password must be 8 characters including 1 uppercase letter, 1 lowercase letter, 1 special character, alphanumeric characters']
            );
            if ($validator->fails()){
                $response["status"] = "400";
                $response["message"] = $validator->errors()->first('password');
                return response($response,400);
            }

            if(!empty($user))
            {
                if($request['display_name']!=""){ $user['display_name'] = $request['display_name']; }
                if($request['password']!=""){
                    $user['password']=encrypt($request['password']);
                }
                if($request['language']!=""){ $user['language'] = $request['language']; }
                $user['is_first_time_login']=0;
                $user->save();
            }
        }
        catch(Exception $e){
            return response("", 500);
        }
    }

    public function token_validation(Request $request)
    {
        try{
            $user = Staff::where("session_token",$request['session_token'])->first();
            if(empty($user) || $request['session_token']==""){
                $response["status"] = "401";
                $response["message"] = "Your account may be logged in from other device or deactivated, please try to login again.";
                return response($response,401);
            }

            $validator = Validator::make($request->all(), [
                    'session_token' => 'required',
                    'language' => 'required',
                ]
            );
            if ($validator->fails()){
                return response("",400);
            }

            if(!empty($user))
            {
                if($request['language']!=""){ $user['language'] = $request['language']; }
                $user->save();
            }
        }
        catch(Exception $e){
            return response("", 500);
        }
    }

    public function update_user_language(Request $request)
    {
        try{
            $user = Staff::where("session_token",$request['session_token'])->first();
            if(empty($user) || $request['session_token']==""){
                $response["status"] = "401";
                $response["message"] = "Your account may be logged in from other device or deactivated, please try to login again.";
                return response($response,401);
            }

            $validator = Validator::make($request->all(), [
                    'session_token' => 'required',
                    'language' => 'required',
                ]
            );
            if ($validator->fails()){
                return response("",400);
            }

            if(!empty($user))
            {
                if($request['language']!=""){ $user['language'] = $request['language']; }
                $user->save();
            }
        }
        catch(Exception $e){
            return response("", 500);
        }
    }
    
//    public function add_attendance(Request $request){
//
//        try {
//                    $odata = json_decode(file_get_contents('php://input'), true);
//                    if($odata) {
//                        if (is_array($odata)) {
//                          $count=count($odata['data']);
//                            for($i=0;$i < $count;$i++) {
//                                $rfid_no=$odata['data'][$i]["tag_id"];
//                                    $student=Student::where('rfid_no',$rfid_no)->first();
//                                    if(!empty($student)){
//                                        $input['school_id']=$student->school_id;
//                                        $input['student_id']=$student->id;
//                                        $input['student_name']=$student->name;
//                                        $input['class_name']=$student->class_id;
//                                        $input['class_division']=$student->division;
//                                        $input['school_in_time']='';
//                                        $input['school_out_time']='';
//                                        $input['staff_id']='';
//                                        $input['staff_name']='';
//                                        $input['status']='active';
//                                    }
//                                    $input['device_id']=$odata['data'][$i]["reader_id"];
//                                    $input['rfid_no']=$rfid_no;
//                                    $input['attendance_date'] = Carbon::parse($odata['data'][$i]["timestamp"])->format('Y-m-d');
//                                    $input['attendance_time'] = Carbon::parse($odata['data'][$i]["timestamp"])->format('H:i:s');
//                                    $attendance = Attendance::create($input);
//                            }
//                        }
//                    }
//        } catch (Exception $e) {
//            return response("", 500);
//        }
//
//    }


//    public function add_attendance(Request $request){
//
//        try {
//            $status="error";
//            $odata = json_decode(file_get_contents('php://input'), true);
//            if($odata) {
//                if (is_array($odata)) {
//                   // return $odata;
//                    $count=count($odata['data']);
//                    for($i=0;$i < $count;$i++) {
//                        $rfid_no=$odata['data'][$i]["tag_id"];
//                        $user=User::where('rfid_no',$rfid_no)->first();
//                        if(!empty($user)){
//                            if($user->role == 'student') {
//                                $input['school_id'] = $user->school_id;
//                                $input['student_id'] = $user->id;
//                                $input['student_name'] = $user->name;
//                                $input['class_name'] = $user->class_id;
//                                $input['class_division'] = $user->division;
//                                $input['school_in_time'] = '';
//                                $input['school_out_time'] = '';
//                                $input['staff_id'] = '';
//                                $input['staff_name'] = '';
//                                $input['staff_role']='';
//                                $input['status'] = 'active';
//                            }
//
//                            if($user->role == 'staff') {
//                                $input['school_id'] = $user->school_id;
//                                $input['student_id'] = $user->id;
//                                $input['student_name'] = $user->name;
//                                $input['class_name'] = '';
//                                $input['class_division'] = '';
//                                $input['school_in_time'] = '';
//                                $input['school_out_time'] = '';
//                                $input['staff_id'] = $user->id;
//                                $input['staff_name'] = $user->name;
//                                $input['staff_role'] = $user->staff_role;
//                                $input['status'] = 'active';
//                            }
//                        }
//                        $input['device_id']=$odata['data'][$i]["reader_id"];
//                        $input['rfid_no']=$rfid_no;
//                        $input['latittude']=$odata['data'][$i]["lat"];
//                        $input['longitude']=$odata['data'][$i]["long"];
//                        $input['attendance_date'] = Carbon::parse($odata['data'][$i]["timestamp"])->format('Y-m-d');
//                        $input['attendance_time'] = Carbon::parse($odata['data'][$i]["timestamp"])->format('H:i:s');
//
//                        $attendance = Attendance::create($input);
//                        if($attendance){
//                            $status="success";
//                        }
//                        else{
//                            $status="error";
//                        }
//                    }
//                }
//            }
//             $response["status"] = $status;
//             return response($response, 200);
//
//        } catch (Exception $e) {
//            return response("", 500);
//        }
//
//    }


    public function add_attendance2(Request $request){

        try {
            $status="error";

            $device_data = Device::select('*')->where('status',"active")->get();
            echo "<pre>";
            #print_r($device_data);
            
            $s = 0;
            foreach($device_data as $device) 
            {

                $s = $device->serial_no;
                if(isset($s) && $s!="")
                {
                  $device_data_array[$s]['id'] = $device->id;
                  $device_data_array[$s]['school_id'] = $device->school_id;
                  $device_data_array[$s]['name'] = $device->name;
                  $device_data_array[$s]['serial_no'] = $device->serial_no;
                  $device_data_array[$s]['location'] = $device->location;
                  $device_data_array[$s]['device_type'] = $device->device_type;
                  $s++;
                }
            }
            
            
            
            #$users=User::select('*')->where('role',"student")->where('status',"active")->get();
            $users=User::select('*')->where('status',"active")->get();
            $s = 0;
            #print_r($users);
            foreach($users as $user) 
            {
                $s = $user->rfid_no;
                if(isset($s) && $s!="")
                {
                  $user_data_array[$s]['id'] = $user->id;
                  $user_data_array[$s]['name'] = $user->name;
                  $user_data_array[$s]['email'] = $user->email;
                  $user_data_array[$s]['school_id'] = $user->school_id;
                  $user_data_array[$s]['class_id'] = $user->class_id;
                  $user_data_array[$s]['division'] = $user->division;
                  $user_data_array[$s]['mobile'] = $user->mobile;
                  $user_data_array[$s]['school_time'] = $user->school_time;
                  $user_data_array[$s]['rfid_no'] = $user->rfid_no;
                  $user_data_array[$s]['role'] = $user->role;
                  $user_data_array[$s]['staff_role'] = $user->staff_role;
                }
                ##$s++;
            }
            
            #echo __FILE__;
            #print_r($device_data_array);
            #print_r($user_data_array);
            
            $this->file_write("student_rfid.json", $user_data_array, true);
            $this->file_write("device_rfid.json", $device_data_array, true);
            echo "File created sucessfully";
            
            
            $device_rfid = $this->file_read("device_rfid.json", $json=true);
            print_r($device_rfid);
            
            $attendence_rfid_data = $this->file_read("attendence_rfid.json", $json=true);
            print_r($attendence_rfid_data);
            exit;
        } catch (Exception $e) {
            return response("", 500);
        }
    }
    
    public function file_write($filename, $content, $json=false) { 
        
        
        if($json){
            $content = json_encode($content);
        }
        $myPublicFolder = public_path();
        #echo "<br>".$myPublicFolder;
        $savePath = $myPublicFolder."/rfid_data/";
        $path = $savePath.$filename;
        #echo "<br>".$path;
        return File::put($path , $content);
        /*
        if (!is_writable($filename)) {
            if (!chmod($filename, 0666)) {
                 echo "Cannot change the mode of file ($filename)";
                 exit;
            };
        }
        if (!$fp = @fopen($filename, "w")) {
            echo "Cannot open file ($filename)";
            exit;
        }
        if (fwrite($fp, $content) === FALSE) {
            echo "Cannot write to file ($filename)";
            exit;
        } 
        if (!fclose($fp)) {
            echo "Cannot close file ($filename)";
            exit;
        }
        */
    }
    
    public function file_rfid_data_write($content) { 
        
        
        
        $myPublicFolder = public_path();
        #echo "<br>".$myPublicFolder;
        $savePath = $myPublicFolder."/rfid_data/";
        
        $filename = "attendence_rfid_".date("Ymd").".json";
        $path = $savePath.$filename;
        #echo "<br>".$path;
        #return File::put($path , $content);
        
        /*
        if (!is_writable($filename)) {
            if (!chmod($filename, 0666)) {
                 echo "Cannot change the mode of file ($filename)";
                 #exit;
            };
        }
        */
        if (!$fp = @fopen($path, "a+")) {
            #echo "Cannot open file ($path)";
            #exit;
        }
        if (fwrite($fp, $content) === FALSE) {
            #echo "Cannot write to file ($path)";
            #exit;
        } 
        if (!fclose($fp)) {
            #echo "Cannot close file ($path)";
            #exit;
        }
        
        
    }
    
    
    public function file_read($filename, $json=false, $content=array()){
        
        $myPublicFolder = public_path();
        #echo "<br>".$myPublicFolder;
        $savePath = $myPublicFolder."/rfid_data/";
        $path = $savePath.$filename;
        
        try{
            if(file_exists($path)){
                $content = File::get($path);
                $content = json_decode($content,$json);
            }
        } catch (Exception $e) {
      
            if(!is_array($content) || count($content)==0){
                $content = array();
            }
            if($json){
                $content = json_encode($content);
            }
            File::put($path , $content);
        }
        
        
        if(!is_array($content) || count($content)==0){
          $content = array();
        }
        return  $content;
        
    } 
            
    public function add_attendance(Request $request){

        try {
            $status="error";
            $file_input_data = file_get_contents('php://input');
            /* store DEVICE JSCON data into file for debugging purpose -  CL*/
            #$this->file_rfid_data_write($file_input_data);
            $odata = json_decode($file_input_data, true);

            if($odata) {
                $device_rfid_data = $this->file_read("device_rfid.json", $json=true);
                $student_rfid_data = $this->file_read("student_rfid.json", $json=true);

                if (is_array($odata)) {
                    // return $odata;
                    $count=count($odata['data']);
                    
                    #$attendence_date_entry = Carbon::parse($odata['data'][0]["timestamp"])->format('Ymd');
                    
                    $attendence_rfid_data = array();
                    $attendence_date_entry_file_name = "student_attendence_rfid_".date("Ymd").".json";
                    $attendence_rfid_data = $this->file_read($attendence_date_entry_file_name, $json=true);
                    #print_r($attendence_rfid_data);
                
                    for($i=0;$i < $count;$i++) {
                        $rfid_no=$odata['data'][$i]["tag_id"];
                        
                        
                        $device_array = array();
                        if(isset($device_rfid_data[$odata['data'][$i]["reader_id"]]) && !empty($device_rfid_data[$odata['data'][$i]["reader_id"]])){
                            $device_array = $device_rfid_data[$odata['data'][$i]["reader_id"]];
                        }
                        
                        if(!is_array($device_array) || count($device_array)==0){
                        
                            $device=Device::where('serial_no',$odata['data'][$i]["reader_id"])->first();
                        }
                        
                        if(!empty($device_array) && $device_array['id'] > 0){
                            $device_id = $device_array['id'];
                        }else if(!empty($device)) {
                            $device_id = $device->id;
                        }
                        else{
                            $device_id=0;
                        }
                        $lat='';
                        $long='';
                        if(isset($odata['data'][$i]["lat"]) && !empty($odata['data'][$i]["lat"])){
                            $lat=$odata['data'][$i]["lat"];
                        }

                        if(isset($odata['data'][$i]["long"]) && !empty($odata['data'][$i]["long"])){
                            $long=$odata['data'][$i]["long"];
                        }
                        $time=Carbon::parse($odata['data'][$i]["timestamp"])->format('H:i:s');

                        $user_array = array();
                        if(isset($student_rfid_data[$rfid_no]) && !empty($student_rfid_data[$rfid_no])){
                            $user_array = $student_rfid_data[$rfid_no];
                        }

                        if(!is_array($user_array) || count($user_array)==0){
                        
                            $user=User::where('rfid_no',$rfid_no)->first();
                        }
                        
                        if(count($user_array)>0 && !empty($user_array)){
                            if($user_array['role'] == 'student') {
                                $input['school_id'] = $user_array['school_id'];
                                $input['student_id'] = $user_array['id'];
                                $input['student_name'] = $user_array['name'];
                                $input['class_name'] = $user_array['class_id'];
                                $input['class_division'] = $user_array['division'];
                                $input['school_in_time'] = $time;
                                $input['school_out_time'] = '';
                                $input['staff_id'] = '';
                                $input['staff_name'] = '';
                                $input['staff_role']='';
                                $input['status'] = 'active';
                            }

                            if($user_array['role'] == 'staff') {
                                $input['school_id'] = $user_array['school_id'];
                                $input['student_id'] = $user_array['id'];
                                $input['student_name'] = $user_array['name'];
                                $input['class_name'] = '';
                                $input['class_division'] = '';
                                $input['school_in_time'] = $time;
                                $input['school_out_time'] = '';
                                $input['staff_id'] = $user_array['id'];
                                $input['staff_name'] = $user_array['name'];
                                $input['staff_role'] = $user_array['staff_role'];
                                $input['status'] = 'active';
                            }
                        }else if(!empty($user)){
                            if($user->role == 'student') {
                                $input['school_id'] = $user->school_id;
                                $input['student_id'] = $user->id;
                                $input['student_name'] = $user->name;
                                $input['class_name'] = $user->class_id;
                                $input['class_division'] = $user->division;
                                $input['school_in_time'] = $time;
                                $input['school_out_time'] = '';
                                $input['staff_id'] = '';
                                $input['staff_name'] = '';
                                $input['staff_role']='';
                                $input['status'] = 'active';
                            }

                            if($user->role == 'staff') {
                                $input['school_id'] = $user->school_id;
                                $input['student_id'] = $user->id;
                                $input['student_name'] = $user->name;
                                $input['class_name'] = '';
                                $input['class_division'] = '';
                                $input['school_in_time'] = $time;
                                $input['school_out_time'] = '';
                                $input['staff_id'] = $user->id;
                                $input['staff_name'] = $user->name;
                                $input['staff_role'] = $user->staff_role;
                                $input['status'] = 'active';
                            }
                        }
                      //  $input['device_id']=$odata['data'][$i]["reader_id"];
                        $input['device_id']=$device_id;
                        $input['rfid_no']=$rfid_no;
                        $input['latittude']=$lat;
                        $input['longitude']=$long;
                        $input['attendance_date'] = Carbon::parse($odata['data'][$i]["timestamp"])->format('Y-m-d');
                        $input['attendance_time'] = $time;

                        $student_check_id = 0;
                        
                        if(isset($attendence_rfid_data[$rfid_no][$input['attendance_date']]) && !empty($attendence_rfid_data[$rfid_no][$input['attendance_date']])){
                            $student_check_id = $attendence_rfid_data[$rfid_no][$input['attendance_date']];
                        }
                        #echo "<br> student_check_id >> $student_check_id";
                        
                        try {

                            if (empty($student_check_id) || $student_check_id==0) {
                            
                                $checkattendance=Attendance::where('rfid_no',$rfid_no)->where('attendance_date',$input['attendance_date'])->first();
                                if (!empty($checkattendance)) {
                                  $student_check_id = $checkattendance->id;
                                  
                                  $attendence_rfid_data[$rfid_no][$input['attendance_date']] =  $student_check_id;
                                  $this->file_write($attendence_date_entry_file_name, $attendence_rfid_data, $json=true);
                                }
                            }
                            
                            if (empty($checkattendance) && (empty($student_check_id) || $student_check_id==0)) {
                                $attendance = Attendance::create($input);
                                $student_check_id= $attendance->id;
                                
                                /* create entry each time when any action happen for RFID device */
                                $input['attendance_id'] = $student_check_id;
                                $attendancedetail = AttendanceDetail::create($input);
                                
                                $attendence_rfid_data[$rfid_no][$input['attendance_date']] =  $student_check_id;
                                $this->file_write($attendence_date_entry_file_name, $attendence_rfid_data, $json=true);
                            } 
                            else 
                            {
                                $attendance = Attendance::findorFail($student_check_id);

                                /*
                                $check_detail = AttendanceDetail::where('device_id',$device_id)->where('rfid_no', $rfid_no)->where('attendance_date', $input['attendance_date'])->first();
                                if (empty($check_detail)) {
                                    $input['attendance_id'] = $attendance->id;
                                    $attendancedetail = AttendanceDetail::create($input);
                                } else {
                                    $attendancedetail = AttendanceDetail::findorFail($check_detail->id);
                                    $input['school_out_time']=$time;
                                    $attendancedetail->update($input);
                                }
                                */
                                
                                
                                /* create entry each time when any action happen for RFID device */
                                $input['attendance_id'] = $student_check_id;
                                $attendancedetail = AttendanceDetail::create($input);

                                if(!empty($lat) && !empty($long)){
                                    $input1['latittude']=$lat;
                                    $input1['longitude']=$long;
                                }
                                $input1['school_out_time'] = $time;

                                $attendance->update($input1);
                            }
                            $status='success';
                        }
                            catch (\Exception $e) {
                                $response["status"] = 'error';
                                return response($response, 200);
                            }
                    }
                }
            }
            $response["status"] = $status;
            return response($response, 200);

        } catch (Exception $e) {
            return response("", 500);
        }
    }
    public function add_attendance_orig(Request $request){

        try {
            $status="error";
            
            $file_input_data = file_get_contents('php://input');
            /* store DEVICE JSCON data into file for debugging purpose -  CL*/
            #$this->file_rfid_data_write($file_input_data);
            $odata = json_decode($file_input_data, true);
            
            if($odata) {

                if (is_array($odata)) {
                    // return $odata;
                    $count=count($odata['data']);
                    for($i=0;$i < $count;$i++) {
                        $rfid_no=$odata['data'][$i]["tag_id"];
                        $device=Device::where('serial_no',$odata['data'][$i]["reader_id"])->first();
                        if(!empty($device)) {
                            $device_id = $device->id;
                        }
                        else{
                            $device_id=0;
                        }
                        $lat='';
                        $long='';
                        if(isset($odata['data'][$i]["lat"]) && !empty($odata['data'][$i]["lat"])){
                            $lat=$odata['data'][$i]["lat"];
                        }

                        if(isset($odata['data'][$i]["long"]) && !empty($odata['data'][$i]["long"])){
                            $long=$odata['data'][$i]["long"];
                        }
                        $time=Carbon::parse($odata['data'][$i]["timestamp"])->format('H:i:s');
                        $user=User::where('rfid_no',$rfid_no)->first();
                        if(!empty($user)){
                            if($user->role == 'student') {
                                $input['school_id'] = $user->school_id;
                                $input['student_id'] = $user->id;
                                $input['student_name'] = $user->name;
                                $input['class_name'] = $user->class_id;
                                $input['class_division'] = $user->division;
                                $input['school_in_time'] = $time;
                                $input['school_out_time'] = '';
                                $input['staff_id'] = '';
                                $input['staff_name'] = '';
                                $input['staff_role']='';
                                $input['status'] = 'active';
                            }

                            if($user->role == 'staff') {
                                $input['school_id'] = $user->school_id;
                                $input['student_id'] = $user->id;
                                $input['student_name'] = $user->name;
                                $input['class_name'] = '';
                                $input['class_division'] = '';
                                $input['school_in_time'] = $time;
                                $input['school_out_time'] = '';
                                $input['staff_id'] = $user->id;
                                $input['staff_name'] = $user->name;
                                $input['staff_role'] = $user->staff_role;
                                $input['status'] = 'active';
                            }
                        }
                      //  $input['device_id']=$odata['data'][$i]["reader_id"];
                        $input['device_id']=$device_id;
                        $input['rfid_no']=$rfid_no;
                        $input['latittude']=$lat;
                        $input['longitude']=$long;
                        $input['attendance_date'] = Carbon::parse($odata['data'][$i]["timestamp"])->format('Y-m-d');
                        $input['attendance_time'] = $time;
                        $checkattendance=Attendance::where('rfid_no',$rfid_no)->where('attendance_date',$input['attendance_date'])->first();
                        try {
                            if (empty($checkattendance)) {
                                $attendance = Attendance::create($input);
//                                if ($attendance) {
//                                    $status = "success";
//                                } else {
//                                    $status = "error";
//                                }
                            } else {
                                $attendance = Attendance::findorFail($checkattendance->id);

                                $check_detail = AttendanceDetail::where('device_id',$device_id)->where('rfid_no', $rfid_no)->where('attendance_date', $input['attendance_date'])->first();
                                if (empty($check_detail)) {
                                    $input['attendance_id'] = $attendance->id;
                                    $attendancedetail = AttendanceDetail::create($input);
                                } else {
                                    $attendancedetail = AttendanceDetail::findorFail($check_detail->id);
                                    $input['school_out_time']=$time;
                                    $attendancedetail->update($input);
                                }

                                if(!empty($lat) && !empty($long)){
                                    $input1['latittude']=$lat;
                                    $input1['longitude']=$long;
                                }
                                $input1['school_out_time'] = $time;

                                $attendance->update($input1);
                            }
                            $status='success';
                        }
                            catch (\Exception $e) {
                                $response["status"] = 'error';
                                return response($response, 200);
                            }
                    }
                }
            }
            $response["status"] = $status;
            return response($response, 200);

        } catch (Exception $e) {
            return response("", 500);
        }
    }
}

