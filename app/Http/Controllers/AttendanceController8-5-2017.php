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
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
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
        $holiday=array();
        $holidays=Calendar::where('school_id',$request['school_id'])->get();
        foreach ($holidays as $k => $hvalue){
            array_push($holiday,$hvalue->holiday_date);
        }
        if(date('N',strtotime($date)) == 7 || in_array($date,$holiday)) {
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

            $student = User::where('role', 'student')->where('school_id', $request['school_id'])->where('class_id', $request['class_name'])->where('division', $request['class_division'])->orderBy('class_id','DESC')->get();
            foreach($student as $key => $value){
                //  $check_present = Attendance::where('student_id',$value->id)->where('school_id', $request['school_id'])->where('class_name', $request['class_name'])->where('class_division', $request['class_division'])->first();
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
            $staff=User::where('role','staff')->where('school_id', $request['school_id'])->get();
            foreach($staff as $key1 => $value1){
                $check_present_staff = Attendance::where('staff_id',$value1->id)->where('attendance_date',$date)->first();
                if(!empty($check_present_staff)){

                    if($check_present_staff->on_leave == 1){
                        array_push($staff_leave_list, $value1->id);
                    }
                    else {
                        array_push($staff_present_list,$value1->id);
                    }

                }
                else{
                    array_push($staff_absent_list,$value1->id);
                }
            }
        }
        else if(isset($request['submit']) && $request['school_id'] != '' && $request['class_name'] != ''){
            $date=$request['date'];
            $student = User::where('role', 'student')->where('school_id', $request['school_id'])->where('class_id', $request['class_name'])->orderBy('class_id','DESC')->get();
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
            $staff=User::where('role','staff')->where('school_id', $request['school_id'])->get();
            foreach($staff as $key1 => $value1){
                $check_present_staff = Attendance::where('staff_id',$value1->id)->where('attendance_date',$date)->first();
                if(!empty($check_present_staff)){

                    if($check_present_staff->on_leave == 1){
                        array_push($staff_leave_list, $value1->id);
                    }
                    else {
                        array_push($staff_present_list,$value1->id);
                    }                }
                else{
                    array_push($staff_absent_list,$value1->id);
                }
            }
        }
        else if(isset($request['submit']) && $request['school_id'] != ''){

            $date=$request['date'];
            $student = Student::where('role', 'student')->where('school_id', $request['school_id'])->orderBy('class_id','DESC')->get();
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

            $staff=User::where('role','staff')->where('school_id', $request['school_id'])->get();
            foreach($staff as $key1 => $value1){
                $check_present_staff = Attendance::where('staff_id',$value1->id)->where('attendance_date',$date)->first();
                if(!empty($check_present_staff))
                {
                    if($check_present_staff->on_leave == 1){
                        array_push($staff_leave_list, $value1->id);
                    }
                    else {
                        array_push($staff_present_list,$value1->id);
                    }                }
                else{
                    array_push($staff_absent_list,$value1->id);
                }
            }
        }
        else{
        }

        if(!empty($request['school_id'] && $request['school_id'] != '')){
            $schol=School::where('id',$request['school_id'])->first();
            if(!empty($schol)) {
                $school_name = $schol->name;
            }
        }

        $data['staff_leave']=count($staff_leave_list);
        $data['staff_present']=count($staff_present_list);
        $data['staff_absent']=count($staff_absent_list);
        $data['total_staff']=count($staff);
        $data['staff_present_list']=$staff_present_list;
        $data['staff_absent_list']=$staff_absent_list;
        $data['staff_leave_list']=$staff_leave_list;
        $data['staff']=$staff;
        $data['leave']=count($leave_list);
        $data['present']=count($present_list);
        $data['absent']=count($absent_list);
        $data['total_student']=count($student);
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
            Notification::create($input1);
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
        \Session::flash('success','Attendance has been inserted successfully!');
        return redirect('attendance');
        // return "staff";
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
