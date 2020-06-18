<?php

namespace App\Http\Controllers;

use App\Attendance;
use App\Class_Master;
use App\Http\Requests;
use App\ParentChild;
use App\School;
use App\Staff;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data=[];
        $data['menu'] = "Dashboard";
        //$data['total_user'] = Staff::count();
        $data['schools'] = School::select('id', 'name')->get();
        if(Auth::user()->role == 'admin') {
            $data['parents'] = User::where('role', 'parent')->count();
            $data['students'] = User::where('role', 'student')->count();
            $data['staff'] = User::where('role', 'staff')->count();
            $data['teacher']=User::where('role', 'staff')->where('staff_role', 'teacher')->count();
            $data['accountant']=User::where('role', 'staff')->where('staff_role', 'accountant')->count();
            $data['peon']=User::where('role', 'staff')->where('staff_role', 'peon')->count();

        }
        else{
            $data['parents'] = User::where('role', 'parent')->where('school_id',Auth::user()->school_id)->count();
            $data['students'] = User::where('role', 'student')->where('school_id',Auth::user()->school_id)->count();
            if(Auth::user()->role == 'parent'){
                $data['child']=ParentChild::where('parent_id',Auth::user()->id)->count();
                //$data['students'] = User::where('role','student')->where('school_id',Auth::user()->school_id)->count();
            }
            $data['staff'] = User::where('role', 'staff')->where('school_id',Auth::user()->school_id)->count();
        }
        if(!empty($request['date'])){
         $date=$request['date'];
        }
        else {
            $date = Carbon::today()->format('Y-m-d');
        }
        $date_facturation=Carbon::parse($date);

        if($date_facturation->isFuture()){
            return back()->withInput()->withErrors(['date' => 'Date is in future please select other date !']);
        }
        foreach($data['schools'] as $key => $school ) {
            $std_present=0;
            $std_leave=0;
             $st_present=0;
             $st_leave=0;
             $tchr_present=0;
             $tchr_leave=0;
             $acc_present=0;
             $acc_leave=0;
             $pn_present=0;
             $pn_leave=0;
            $total_class = Class_Master::where('school_id', $school->id)->count();
            $total_student = User::where('school_id', $school->id)->where('role','student')->count();
            $total_staff = User::where('school_id', $school->id)->where('role','staff')->count();
            $total_teacher = User::where('school_id', $school->id)->where('role','staff')->where('staff_role', 'teacher')->count();
            $total_accountant = User::where('school_id', $school->id)->where('role','staff')->where('staff_role', 'accountant')->count();
            $total_peon = User::where('school_id', $school->id)->where('role','staff')->where('staff_role', 'peon')->count();

            $data['schools'][$key]['class'] = $total_class;
            $data['schools'][$key]['student'] = $total_student;
            $data['schools'][$key]['staff'] = $total_staff;
            $data['schools'][$key]['teacher'] = $total_teacher;
            $data['schools'][$key]['accountant'] = $total_accountant;
            $data['schools'][$key]['peon'] = $total_peon;

            $stud_present = Attendance::where('school_id', $school->id)->where('attendance_date',$date)
                ->where('student_id', '>' ,0)->get();
            foreach($stud_present as $key001 => $value){
                if($value->on_leave == 1){
                    $std_leave++;
                }
                else{
                    $std_present++;
                }
            }

            $std_absent = $total_student - $std_present - $std_leave;

            $data['schools'][$key]['student_present'] = $std_present;
            $data['schools'][$key]['student_leave'] = $std_leave;
            $data['schools'][$key]['student_absent'] = $std_absent;

             $staff_present = Attendance::where('school_id', $school->id)->where('attendance_date',$date)
                ->where('staff_id', '>' ,0)->get();

            foreach($staff_present as $key0 => $value){
                if($value->on_leave == 1){
                    $st_leave++;
                }
                else{
                    $st_present++;
                }
            }
           // return $st_leave;
            $teacher_present = Attendance::where('school_id', $school->id)->where('attendance_date',$date)
                ->where('staff_id', '>' ,0)->where('staff_role', 'teacher')->get();
            foreach($teacher_present as $key1 => $value1){
                if($value1->on_leave == 1){
                    $tchr_leave++;
                }
                else{
                    $tchr_present++;
                }
            }
            $accountant_present = Attendance::where('school_id', $school->id)->where('attendance_date',$date)
                ->where('staff_id', '>' ,0)->where('staff_role', 'accountant')->get();
            foreach($accountant_present as $key2 => $value2){
                if($value2->on_leave == 1){
                    $acc_leave++;
                }
                else{
                    $acc_present++;
                }
            }
            $peon_present = Attendance::where('school_id', $school->id)->where('attendance_date',$date)
                ->where('staff_id', '>' ,0)->where('staff_role', 'peon')->get();
            foreach($peon_present as $key2 => $value2){
                if($value2->on_leave == 1){
                    $pn_leave++;
                }
                else{
                    $pn_present++;
                }
            }

            $st_absent = $total_staff - $st_present - $st_leave;
            $data['schools'][$key]['staff_present'] = $st_present;
            $data['schools'][$key]['staff_leave'] = $st_leave;
            $data['schools'][$key]['staff_absent'] = $st_absent;

            $tchr_absent = $total_teacher - $tchr_present - $tchr_leave;
            $data['schools'][$key]['teacher_present'] = $tchr_present;
            $data['schools'][$key]['teacher_leave'] = $tchr_leave;
            $data['schools'][$key]['teacher_absent'] = $tchr_absent;

            $acc_absent = $total_accountant - $acc_present - $acc_leave;
            $data['schools'][$key]['accountant_present'] = $acc_present;
            $data['schools'][$key]['accountant_leave'] = $acc_leave;
            $data['schools'][$key]['accountant_absent'] = $acc_absent;

            $pn_absent = $total_peon - $pn_present - $pn_leave;
            $data['schools'][$key]['peon_present'] = $pn_present;
            $data['schools'][$key]['peon_leave'] = $pn_leave;
            $data['schools'][$key]['peon_absent'] = $pn_absent;
        }
        return view("dashboard",$data);
    }

    public function child()
    {
        $data=[];
        $data['menu'] = "Dashboard";
        $data['parent_child']=ParentChild::where('parent_id',Auth::user()->id)->get();
        return view('child',$data);
    }
}