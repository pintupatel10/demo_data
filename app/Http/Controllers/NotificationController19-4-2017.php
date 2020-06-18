<?php

namespace App\Http\Controllers;

use App\Notification;
use App\ParentChild;
use App\Parents;
use App\School;
use App\User;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;


class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('staff');
    }

    public function index()
    {
        $data=[];
        $data['menu']="Notification";
        $data['school']=School::lists('name','id')->all();
        return view('notification.add', $data);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'school_id' => 'required',
            'message' => 'required',
            'notification_type' => 'required',
            'notification_to' => 'required',

        ]);
        $input = $request->all();
        $sender= Auth::user()->id;
        if(Auth::user()->staff_role == 'peon'){
            Session::flash('message', 'Unauthorise user to send notification!');
            Session::flash('alert-class', 'alert-danger');
            return redirect('notification');
        }
        if($request['notification_to'] == 'All'){
            $user=User::where('role','!=','student')->where('school_id',$request['school_id'])->where('id','!=',$sender)->get();
        }
        else if($request['notification_to'] == 'Staff'){
            $user=User::where('role','staff')->where('school_id',$request['school_id'])->where('id','!=',$sender)->get();
        }

        else if($request['notification_to'] == 'Parents'){
            $user = new \Illuminate\Database\Eloquent\Collection;
            if($request['student_id'] != ''){
               $user = ParentChild::select('parent_id')->where('student_id',$request['student_id'])->get();
                 // return $user;
             }
           else if ($request['school_id'] != '' && $request['class_name'] != '' && $request['class_division'] != '') {
                $student = User::select('id')->where('role','student')->where('school_id', $request['school_id'])->where('class_id', $request['class_name'])->where('division', $request['class_division'])->get();
               foreach($student as $value) {
                   $parent_child=ParentChild::select('parent_id')->where('student_id',$value->id)->first();
                   if(!empty($parent_child)) {
                       $user->add($parent_child);
                   }
               }
           } else if ($request['school_id'] != '' && $request['class_name'] != '') {
               $student =  User::select('id')->where('role','student')->where('school_id', $request['school_id'])->where('class_id', $request['class_name'])->get();
               foreach($student as $value) {
                   $parent_child=ParentChild::select('parent_id')->where('student_id',$value->id)->first();
                   if(!empty($parent_child)) {
                       $user->add($parent_child);
                   }
               }
           }
           else if ($request['school_id'] != '') {
               $student = User::select('id')->where('role','student')->where('school_id', $request['school_id'])->get();
               foreach($student as $value) {
                   $parent_child=ParentChild::select('parent_id')->where('student_id',$value->id)->first();
                   if(!empty($parent_child)) {
                       $user->add($parent_child);
                   }
               }
           }
           else {
               $user = new \Illuminate\Database\Eloquent\Collection;
           }

        }
        else{
            $user = new \Illuminate\Database\Eloquent\Collection;
        }

        if($request['notification_type'] != ''){
            foreach($request['notification_type'] as $type){
                if($type == 'notification'){
                    foreach($user as $key=> $value){
                        if($request['notification_to'] == 'Parents'){
                            $receiver=$value->parent_id;
                        }
                        else {
                            $receiver = $value->id;
                        }
                        $input['type']=$type;
                        $input['receiver_id']=$receiver;
                        $input['sender_id']=$sender;
                        $input['message']=$request['message'];
                        Notification::create($input);
                    }
                }
                if($type == 'sms'){
                    foreach($user as $key=> $value){
                        if($request['notification_to'] == 'Parents'){
                            $receiver=$value->parent_id;
                        }
                        else {
                            $receiver = $value->id;
                        }
                        $input['type']=$type;
                        $input['receiver_id']=$receiver;
                        $input['sender_id']=$sender;
                        $input['message']=$request['message'];
                        Notification::create($input);
                    }
                }
            }
        }

//        $user = AppUser::where('is_verified',1)->get();
//        foreach ($user as $key => $value){
//            $deviceid = $value['deviceToken'];
//            $device_char =  strlen($value['deviceToken']);
//            if ($value['deviceToken']!="" && $device_char >=20 ) {
//                PushNotification::app('appNameIOS')
//                    ->to($deviceid)
//                    ->send($message);
//            }
//            $input['user_id']=$value->id;
//            $input['message']=$request['message'];
//            $input['type']=6;
//            $noti=Notification::create($input);
//        }

        \Session::flash('success', 'Notification send successfully.');
        return redirect('notification');
    }
}
