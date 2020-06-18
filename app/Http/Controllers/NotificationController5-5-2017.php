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
        //$user=User::where('id',$sender)->first();
        if(Auth::user()->staff_role == 'peon'){
            Session::flash('message', 'Unauthorise user to send notification!');
            return redirect('notification');
        }

        if($request['notification_to'] == 'All'){
            if(Auth::user()->staff_role == 'principal' || Auth::user()->staff_role == 'teacher') {
                Session::flash('message', 'You are not allow to send notifications to All!');
                return redirect('notification');
            }
            $user_notification=User::where('role','!=','student')->where('school_id',$request['school_id'])->where('id','!=',$sender)->get();
            $user_sms=User::where('school_id',$request['school_id'])->where('id','!=',$sender)->get();
        }

        else if($request['notification_to'] == 'Principal'){

            if(Auth::user()->staff_role == 'principal' || Auth::user()->staff_role == 'teacher') {
                Session::flash('message', 'You are not allow to send notifications to Principal!');
                return redirect('notification');
            }
            $user_notification=User::where('role','staff')->where('staff_role','principal')->where('school_id',$request['school_id'])->where('id','!=',$sender)->get();
            $user_sms=User::where('role','staff')->where('staff_role','principal')->where('school_id',$request['school_id'])->where('id','!=',$sender)->get();

        }
        else if($request['notification_to'] == 'Teacher'){

            if(Auth::user()->staff_role == 'teacher') {
                Session::flash('message', 'You are not allow to send notifications to Teacher!');
                return redirect('notification');
            }
            $user_notification=User::where('role','staff')->where('staff_role','teacher')->where('school_id',$request['school_id'])->where('id','!=',$sender)->get();
            $user_sms=User::where('role','staff')->where('staff_role','teacher')->where('school_id',$request['school_id'])->where('id','!=',$sender)->get();

        }

        else if($request['notification_to'] == 'Student'){
            $user_notification = new \Illuminate\Database\Eloquent\Collection;

            if($request['student_id'] != ''){
                $user_sms = User::where('id',$request['student_id'])->get();
            }
            else if ($request['school_id'] != '' && $request['class_name'] != '' && $request['class_division'] != '') {
                $user_sms = User::where('role','student')->where('school_id', $request['school_id'])->where('class_id', $request['class_name'])->where('division', $request['class_division'])->get();
            } else if ($request['school_id'] != '' && $request['class_name'] != '') {
                $user_sms =  User::where('role','student')->where('school_id', $request['school_id'])->where('class_id', $request['class_name'])->get();

            }
            else if ($request['school_id'] != '') {
                $user_sms = User::where('role','student')->where('school_id', $request['school_id'])->get();
            }
            else {
                $user_sms = new \Illuminate\Database\Eloquent\Collection;
            }
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
        }
        else{
            $user_notification = new \Illuminate\Database\Eloquent\Collection;
            $user_sms = new \Illuminate\Database\Eloquent\Collection;
        }
        if($request['notification_type'] != ''){
            foreach($request['notification_type'] as $type){

                if($type == 'sms'){
                    $this->validate($request, [
                        'sms_type' => 'required',
                    ]);
                    $final_list='';
                    $msg='';
                    $list='';

                    if($request['sms_type'] == 'promotional' ){
                        $msg=$request['message'];
                        $sid='WEBSMS';
                        $msg_type='';
                    }

                    if($request['sms_type'] == 'transactional' ){
                        $sid='WEBSMS';
                        $msg_type='&gwid=2';
                        $present='Dear Parents , Your Child Chirag janjmera has come to school at 9:30am 23 March 2017';
                        $absent='';
                        $holiday='';
                        $emergency_close='';
                        $injured='';
                        $vacation='';
                       // return $request['sms_type'];
                    }

                    foreach($user_sms as $key11=> $value11){
                        if(isset($value11->mobile) &&  strlen($value11->mobile) == 10) {
                            $list.=$value11->mobile.',';
                            $receiver = $value11->id;
                            $input['type'] = $type;
                            $input['receiver_id'] = $receiver;
                            $input['sender_id'] = $sender;
                            $input['message'] = $request['message'];
                            Notification::create($input);
                        }
                        $final_list=trim($list,',');
                        $msg=$request['message'];
                    }

                    $ch = curl_init();// init curl
                    curl_setopt($ch, CURLOPT_URL,"http://bulksms.alayada.com/vendorsms/pushsms.aspx");
                    curl_setopt($ch, CURLOPT_POST, 1);// set post data to true
                    curl_setopt($ch, CURLOPT_POSTFIELDS,"user=chetanjsheth&password=C123123H1212&msisdn=".$final_list."&sid=".$sid."&msg=".$msg."&fl=0".$msg_type."");// post data
                    // receive server response ...
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);// gives you a response from the server

                    $response = curl_exec ($ch);// response it ouputed in the response var

                    curl_close ($ch);

                    if($request['notification_to'] == 'Student'){
                        Session::flash('message', 'SMS sent to Student!');
                        return redirect('notification');
                    }
                }

                if($type == 'notification'){
                    if($request['notification_to'] == 'Student'){
                        Session::flash('message', 'Students can get only SMS, You can not send any notifications to student!');
                        return redirect('notification');
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
                            $device_char_android = strlen($value['deviceToken']);
                            if (!empty($value['deviceToken']) && $value['deviceToken'] != "" && $device_char_android >= 20) {
                                \Davibennun\LaravelPushNotification\Facades\PushNotification::app('appNameAndroid')
                                   // ->to($value['deviceToken'])
                                    ->to('dlxC01Kaftk:APA91bGchRgFW7u0_mSOOszLqWf5ehyuHQMQKtCw9CTX2GstjW5NTl3CnmJcVVxgiYMSrTXNMuxk1dsg5AO-ZkMEWcf_0HdbS60TqDcYh94vcAzddM0k7SBqcada9Jm38sXqPTbiUNZF')
                                    ->send($input['message']);
                            }
                        }
                        Notification::create($input);
                    }
                }
            }
        }

//        $user_notification = AppUser::where('is_verified',1)->get();
//        foreach ($user_notification as $key => $value){
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
