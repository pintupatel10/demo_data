<?php

namespace App\Http\Controllers;

use App\Attendance;
use App\Calendar;
use App\Class_Master;
use App\Device;
use App\Division;
use App\Http\Middleware\Staff;
use App\Notification;
use App\ParentChild;
use App\Parents;
use App\School;
use App\Student;
use App\User;
use Carbon\Carbon;
use Edujugon\PushNotification\PushNotification;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('staff');
    }

    public function index(Request $request)
    {
        $data=[];
        $date=$request['date'];

        $date_facturation=Carbon::parse($request['date']);

        if($date_facturation->isFuture()){
            return back()->withInput()->withErrors(['date' => 'Date is in future please select other date !']);
        }
        $holiday = Calendar::where('school_id', $request['school_id'])->pluck('holiday_date')->toArray();

        if(in_array($date,$holiday)) {
            Session::flash('message', 'There is Holiday on '.$date.', No records found!');
            return redirect('attendance');
        }

        $student= new \Illuminate\Database\Eloquent\Collection;
        $staff=new \Illuminate\Database\Eloquent\Collection;
        $school_name='';
        // $date='';
        $leave_list=array();
        $present_list=array();
        $absent_list=array();
        $staff_leave_list=array();
        $staff_present_list=array();
        $staff_absent_list=array();
        $data['menu'] = "Attendance";
        $data['school_name'] = School::lists('name','id')->all();
        if(isset($request['submit']) && $request['school_id'] != '' && $request['class_name'] != '' && $request['class_division'] != '') {
            $date=$request['date'];
            if(!empty($request['search_student'])){
                $student  = Student::where(function($query) use ($request)
                {
                    $query->where('role', 'student')->where('school_id', $request['school_id'])->where('class_id', $request['class_name'])->where('division', $request['class_division'])->orderBy('class_id', 'DESC');
                })
                    ->Where(function($query) use ($request)
                    {
                        $query->orWhere('name', 'like', '%'.$request['search_student'].'%')
                            ->orWhere('email', 'like', '%'.$request['search_student'].'%')
                            ->orWhere('parents_name', 'like', '%'.$request['search_student'].'%')
                            ->orWhere('blood_group', 'like', '%'.$request['search_student'].'%');
                    })->Paginate($this->pagination);
            }
            else {
                $student = Student::where('role', 'student')->where('school_id', $request['school_id'])->where('class_id', $request['class_name'])->where('division', $request['class_division'])->orderBy('class_id', 'DESC')->Paginate($this->pagination);
            }
            foreach($student as $key => $value){
                $check_present = Attendance::where('student_id',$value->id)->where('attendance_date',$date)->first();

                if(!empty($check_present)){
                    if($check_present->on_leave == 1){
                        array_push($leave_list, $value->id);
                    }
                    else {
                        array_push($present_list, $value->id);
                    }
                }
                else{
                    array_push($absent_list,$value->id);
                }
            }
            $student_total_count=Student::where('role','student')->where('school_id', $request['school_id'])->where('class_id', $request['class_name'])->where('division', $request['class_division'])->count();
            $student_present_count=Attendance::where('student_id','!=',0)->where('on_leave',0)->where('attendance_date',$date)->where('school_id', $request['school_id'])->where('class_name', $request['class_name'])->where('class_division', $request['class_division'])->count();
            $student_leave_count=Attendance::where('student_id','!=',0)->where('on_leave',1)->where('attendance_date',$date)->where('school_id', $request['school_id'])->where('class_name', $request['class_name'])->where('class_division', $request['class_division'])->count();
        }
        else if(isset($request['submit']) && $request['school_id'] != '' && $request['class_name'] != ''){
            $date=$request['date'];

            if(!empty($request['search_student'])){
                $student  = Student::where(function($query) use ($request)
                {
                    $query->where('role', 'student')->where('school_id', $request['school_id'])->where('class_id', $request['class_name'])->orderBy('class_id', 'DESC');
                })
                    ->Where(function($query) use ($request)
                    {
                        $query->orWhere('name', 'like', '%'.$request['search_student'].'%')
                            ->orWhere('email', 'like', '%'.$request['search_student'].'%')
                            ->orWhere('parents_name', 'like', '%'.$request['search_student'].'%')
                            ->orWhere('blood_group', 'like', '%'.$request['search_student'].'%');
                    })->Paginate($this->pagination);
            }
            else {
                $student = Student::where('role', 'student')->where('school_id', $request['school_id'])->where('class_id', $request['class_name'])->orderBy('class_id','DESC')->Paginate($this->pagination);
            }

            foreach($student as $key => $value){
                $check_present = Attendance::where('student_id',$value->id)->where('attendance_date',$date)->first();

                if(!empty($check_present)){
                    if($check_present->on_leave == 1){
                        array_push($leave_list, $value->id);
                    }
                    else {
                        array_push($present_list, $value->id);
                    }                }
                else{
                    array_push($absent_list,$value->id);
                }
            }
            $student_total_count=User::where('role','student')->where('school_id', $request['school_id'])->where('class_id', $request['class_name'])->count();
            $student_present_count=Attendance::where('student_id','!=',0)->where('on_leave',0)->where('attendance_date',$date)->where('school_id', $request['school_id'])->where('class_name', $request['class_name'])->count();
            $student_leave_count=Attendance::where('student_id','!=',0)->where('on_leave',1)->where('attendance_date',$date)->where('school_id', $request['school_id'])->where('class_name', $request['class_name'])->count();

        }
        else if(isset($request['submit']) && $request['school_id'] != ''){

            $date=$request['date'];

            if(!empty($request['search_student'])){
                $data['search_student']=$request['search_student'];
                $student  = Student::where(function($query) use ($request)
                {
                    $query->where('role', 'student')->where('school_id', $request['school_id'])->orderBy('class_id', 'DESC');
                })
                    ->Where(function($query) use ($request)
                    {
                        $query->orWhere('name', 'like', '%'.$request['search_student'].'%')
                            ->orWhere('email', 'like', '%'.$request['search_student'].'%')
                            ->orWhere('parents_name', 'like', '%'.$request['search_student'].'%')
                            ->orWhere('blood_group', 'like', '%'.$request['search_student'].'%');
                    })->Paginate($this->pagination);
            }
            else {
                $student = Student::where('role', 'student')->where('school_id', $request['school_id'])->orderBy('class_id','DESC')->Paginate($this->pagination);
            }

            foreach($student as $key => $value){
                $check_present = Attendance::where('student_id',$value->id)->where('attendance_date',$date)->first();
                if(!empty($check_present)){
                    if($check_present->on_leave == 1){
                        array_push($leave_list, $value->id);
                    }
                    else {
                        array_push($present_list, $value->id);
                    }
                }
                else{
                    array_push($absent_list,$value->id);
                }
            }
            $student_total_count=User::where('role','student')->where('school_id', $request['school_id'])->count();
            $student_present_count=Attendance::where('student_id','!=',0)->where('on_leave',0)->where('attendance_date',$date)->where('school_id', $request['school_id'])->count();
            $student_leave_count=Attendance::where('student_id','!=',0)->where('on_leave',1)->where('attendance_date',$date)->where('school_id', $request['school_id'])->count();
        }
        else{
            $student_total_count=0;
            $student_present_count=0;
            $student_leave_count=0;
        }

        if(!empty($request['school_id'] && $request['school_id'] != '')){
            $schol=School::where('id',$request['school_id'])->first();
            if(!empty($schol)) {
                $school_name = $schol->name;
            }

            /*stsff atendance*/

            if(!empty($request['search_staff'])){
                $data['search_staff']=$request['search_staff'];
                $staff  = User::where(function($query) use ($request)
                {
                    $query->where('role', 'staff')->where('school_id', $request['school_id']);
                })
                    ->Where(function($query) use ($request)
                    {
                        $query->orWhere('name', 'like', '%'.$request['search_staff'].'%')
                            ->orWhere('email', 'like', '%'.$request['search_staff'].'%')
                            ->orWhere('blood_group', 'like', '%'.$request['search_staff'].'%');
                    })->Paginate($this->pagination);
            }
            else {
                $staff = User::where('role', 'staff')->where('school_id', $request['school_id'])->Paginate($this->pagination);
            }

            foreach ($staff as $key1 => $value1) {
                $check_present_staff = Attendance::where('staff_id', $value1->id)->where('attendance_date', $date)->first();
                if (!empty($check_present_staff)) {

                    if ($check_present_staff->on_leave == 1) {
                        array_push($staff_leave_list, $value1->id);
                    } else {
                        array_push($staff_present_list, $value1->id);
                    }
                } else {
                    array_push($staff_absent_list, $value1->id);
                }
            }
            $staff_total_count=User::where('role','staff')->where('school_id', $request['school_id'])->count();
            $staff_present_count=Attendance::where('staff_id','!=',0)->where('on_leave',0)->where('attendance_date', $date)->count();
            $staff_leave_count = Attendance::where('staff_id','!=',0)->where('on_leave',1)->where('attendance_date', $date)->count();
        }
        else{
            $staff_total_count=0;
            $staff_present_count=0;
            $staff_leave_count=0;
        }

        $data['total_staff']=$staff_total_count;
        $data['staff_leave']=$staff_leave_count;
        $data['staff_present']=$staff_present_count;
        $data['staff_absent']=$staff_total_count - $staff_leave_count - $staff_present_count;
        $data['staff_present_list']=$staff_present_list;
        $data['staff_absent_list']=$staff_absent_list;
        $data['staff_leave_list']=$staff_leave_list;
        $data['staff']=$staff;
        $data['total_student']=$student_total_count;
        $data['leave']=$student_leave_count;
        $data['present']=$student_present_count;
        $data['absent']=$student_total_count - $student_leave_count - $student_present_count;
        $data['present_list']=$present_list;
        $data['absent_list']=$absent_list;
        $data['leave_list']=$leave_list;
        $data['student']=$student;
        $data['date']=$date;
        $data['schoolname']=$school_name;
        $data['all_class']=Class_Master::where('school_id',$request['school_id'])->lists('name','id')->all();
        $data['all_division']=Division::where('school_id',$request['school_id'])->where('class_id',$request['class_name'])->lists('division','id')->all();

        return view('attendance.index', $data);
    }

    public function store(Request $request)
    {
        $input = $request->all();

        $this->validate($request, [
            'school_id' => 'required',
            'class_name' => 'required',
            'class_division' => 'required',
            'student_id' => 'required',
            //'school_in_time' => 'required',
            //'school_out_time' => 'required',
            'attendance_date' => 'required',
            'attendance_time' => 'required',
            'status' => 'required',
        ]);

        // $input['staff_id']=Auth::user()->id;
        // $input['staff_name']=Auth::user()->name;

        $device=Device::where('school_id',$request['school_id'])->where('device_type','main gate')->first();

        if(!empty($device)){
            //$device_id=$device->serial_no;
            $device_id=$device->id;
        }
        else{
            $device_id=0;
        }
        $input['device_id']=$device_id;
        if(!empty($request['on_leave'])){
            $input['on_leave']=$request['on_leave'];
            $input['school_in_time']='';
            $input['school_out_time']='';
        }
        else{
            $input['on_leave']=0;
        }
        $input['staff_role']='';
        Attendance::create($input);

        /*send notification or sms if any remark is added*/
        if(!empty($request['remark']) && $request['remark'] != ''){
            $input1['sender_id']=Auth::user()->id;
            $parent=ParentChild::where('student_id',$request['student_id'])->first();
            if(!empty($parent)){
                $parent_id=$parent->parent_id;
            }
            else{
                $parent_id=0;
            }
            $input1['receiver_id']=$parent_id;
            $input1['message']=$request['remark'];

             $user=User::where('id',$parent_id)->first();
            if(!empty($user)) {
                if ($request['notification_type'] == 'both') {

                    if ($user['device_type'] == 'android') {
                        $device_char = strlen($user['deviceToken']);
                        if ($user['deviceToken'] != "" && $device_char >= 20) {
                            \Edujugon\PushNotification\Facades\PushNotification::setService('fcm')
                                ->setMessage([
                                    'notification' => [
                                        'title' => Auth::user()->name . ' message from School',
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
                    curl_setopt($ch, CURLOPT_URL,"http://bulksms.alayada.com/vendorsms/pushsms.aspx");
                    curl_setopt($ch, CURLOPT_POST, 1);// set post data to true
                    curl_setopt($ch, CURLOPT_POSTFIELDS,"user=chetanjsheth&password=C123123H1212&msisdn=".$user['mobile']."&sid=WEBSMS&msg=".$input1['message']."&fl=0");// post data
                    // receive server response ...
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);// gives you a response from the server
                    $response = curl_exec ($ch);// response it ouputed in the response var
                    curl_close ($ch);

                }
                if ($request['notification_type'] == 'sms') {

                    $ch = curl_init();// init curl
                    curl_setopt($ch, CURLOPT_URL,"http://bulksms.alayada.com/vendorsms/pushsms.aspx");
                    curl_setopt($ch, CURLOPT_POST, 1);// set post data to true
                    curl_setopt($ch, CURLOPT_POSTFIELDS,"user=chetanjsheth&password=C123123H1212&msisdn=".$user['mobile']."&sid=WEBSMS&msg=".$input1['message']."&fl=0");// post data
                    // receive server response ...
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);// gives you a response from the server
                    $response = curl_exec ($ch);// response it ouputed in the response var
                    curl_close ($ch);
                }
                if ($request['notification_type'] == 'notification') {
                    /* ANDROID PUSH NOTIFICATION */
                    if ($user['device_type'] == 'android') {
                        $device_char = strlen($user['deviceToken']);
                        if ($user['deviceToken'] != "" && $device_char >= 20) {
                                \Edujugon\PushNotification\Facades\PushNotification::setService('fcm')
                                    ->setMessage([
                                        'notification' => [
                                            'title' => Auth::user()->name . ' message from School',
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
        \Session::flash('success','Attendance has been inserted successfully!');
        return redirect('attendance');
    }

    public function staff_attendance(Request $request){

        $input = $request->all();
        $this->validate($request, [
            //'school_in_time' => 'required',
            //'school_out_time' => 'required',

        ]);
        $staff=User::where('role','staff')->where('id',$request['staff_id'])->first();
        if(!empty($staff)){
            $input['staff_role']=$staff->staff_role;
        }
        else{
            $input['staff_role']='';
        }
        $device=Device::where('school_id',$request['school_id'])->where('device_type','gate')->first();
        if(!empty($device)){
            $device_id=$device->id;
        }
        else{
            $device_id=0;
        }
        $input['device_id']=$device_id;

        if(!empty($request['on_leave'])){
            $input['on_leave']=$request['on_leave'];
            $input['school_in_time']='';
            $input['school_out_time']='';
        }
        else{
            $input['on_leave']=0;
        }

        Attendance::create($input);
        /*send notification or sms if any remark is added*/
        if(!empty($request['remark']) && $request['remark'] != '') {
            if (!empty($staff)) {

                $input1['sender_id'] = Auth::user()->id;
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
                                            'title' => Auth::user()->name . ' message from School',
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
                        $response = curl_exec($ch);// response it ouputed in the response var
                        curl_close($ch);

                    }
                    if ($request['notification_type'] == 'sms') {

                        $ch = curl_init();// init curl
                        curl_setopt($ch, CURLOPT_URL, "http://bulksms.alayada.com/vendorsms/pushsms.aspx");
                        curl_setopt($ch, CURLOPT_POST, 1);// set post data to true
                        curl_setopt($ch, CURLOPT_POSTFIELDS, "user=chetanjsheth&password=C123123H1212&msisdn=" . $staff['mobile'] . "&sid=WEBSMS&msg=" . $input1['message'] . "&fl=0");// post data
                        // receive server response ...
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);// gives you a response from the server
                        $response = curl_exec($ch);// response it ouputed in the response var
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
                                            'title' => Auth::user()->name . ' message from School',
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
        \Session::flash('success','Attendance has been inserted successfully!');
        return redirect('attendance');

    }

    public function show($id)
    {
        $data=[];
        $data['menu']="Attendance";
        $data['attendance'] = Attendance::with('AttendanceDetail')->findorFail($id);
        return view('attendance.view',$data);
    }

    public function edit($id)
    {
        $data=[];
        $data['menu'] = "Attendance";
        $data['school_name'] = School::lists('name','id')->all();
        $data['attendance'] = Attendance::findorFail($id);
        $data['modes_student'] = Student::where('id',$data['attendance']['student_id'])->lists('name','id');
        $data['modes_class_name'] = Class_Master::where('id',$data['attendance']['class_name'])->lists('name','id');
        $data['modes_class_division'] =Division::where('class_id',$data['attendance']['class_name'])->lists('division','division');
        return view('attendance.edit',$data);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'school_id' => 'required',
            'class_name' => 'required',
            'class_division' => 'required',
            'student_id' => 'required',
            'school_in_time' => 'required',
            'school_out_time' => 'required',
            'attendance_date' => 'required',
            'attendance_time' => 'required',
            'status' => 'required',
        ]);

        $attendance = Attendance::findorFail($id);
        $input = $request->all();
        $input['staff_id']=Auth::user()->id;
        $input['staff_name']=Auth::user()->name;
        if(!empty($request->on_leave)){
            $input['on_leave']=$request['on_leave'];
            $input['school_in_time']='';
            $input['school_out_time']='';
        }
        else{
            $input['on_leave']=0;
        }
        $attendance->update($input);
        \Session::flash('success','Attendance has been updated successfully!');
        return redirect('attendance');
    }

    public function destroy($id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->delete();
        \Session::flash('danger','Attendance has been deleted successfully!');
        return redirect('attendance');
    }

    public function get_student($class_id,$school_id)
    {
        $student = Student::where('school_id',$school_id)->where('class_id',$class_id)->where('role','student')->get();
        echo "<option value=''>Please Select</option>";
        foreach ($student as $student1){
            echo '<option value="'.$student1['id'].'">'.$student1['name'].'</option>';
        }
        return "";
    }
    public function get_classes($id)
    {
        $classes = Class_Master::where('school_id',$id)->get();
        echo "<option value=''>Please Select</option>";
        foreach ($classes as $class1){
            echo '<option value="'.$class1['id'].'">'.$class1['name'].'</option>';
        }

        return "";
    }

    public function get_division($class_id,$school_id)
    {
        $classes = Division::where('school_id',$school_id)->where('class_id',$class_id)->get();
        echo "<option value=''>Please Select</option>";
        foreach ($classes as $class1){
            echo '<option value="'.$class1['division'].'">'.$class1['division'].'</option>';
        }

        return "";
    }
}
