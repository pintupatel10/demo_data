<?php

namespace App\Http\Controllers;

use App\Notification;
use App\ParentChild;
use App\Parents;
use App\School;
use App\User;
use Carbon\Carbon;
use Edujugon\PushNotification\Facades\PushNotification;
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
        $this->middleware('principal');

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
            //'message' => 'required',
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

            $present = 'You are  present in  school on '.$request['date'];
            $absent = 'You are remain absent on '.$request['date'];
            $holiday = 'There is holiday in our school on  '.$request['date'];
            $emergency_close = 'School will be closed on '.$request['date'].' , All students  are not expected to report to school , All after school activities have been cancelled.';
            //$injured = '';
            $vacation = 'School is remain close till '.$request['date'].' because of vacation. New semester will be started from '.Carbon::parse($request['date'] )->modify('+1 day')->format('Y-m-d');
        }

        else if($request['notification_to'] == 'Principal'){

            if(Auth::user()->staff_role == 'principal' || Auth::user()->staff_role == 'teacher') {
                Session::flash('message', 'You are not allow to send notifications to Principal!');
                return redirect('notification');
            }

            $user_notification=User::where('role','staff')->where('staff_role','principal')->where('school_id',$request['school_id'])->where('id','!=',$sender)->get();
            $user_sms=User::where('role','staff')->where('staff_role','principal')->where('school_id',$request['school_id'])->where('id','!=',$sender)->get();

            $present = 'Dear Principal , You are  present in  school on '.$request['date'];
            $absent = 'Dear Principal , You remain absent on 23 '.$request['date'];
            $holiday = 'Dear Principal , there is holiday in our school on '. $request['date'];
            $emergency_close = 'Dear Principal , school will be closed on '.$request['date'].', All students  are not expected to report to school , All after school activities have been cancelled.';
           // $injured = '';
            $vacation = 'Dear Principal school is remain close '.$request['date'].' because of vacation. New semester will be started from '.Carbon::parse($request['date'] )->modify('+1 day')->format('Y-m-d');

        }
        else if($request['notification_to'] == 'Teacher'){

            if(Auth::user()->staff_role == 'teacher') {
                Session::flash('message', 'You are not allow to send notifications to Teacher!');
                return redirect('notification');
            }
            $user_notification=User::where('role','staff')->where('staff_role','teacher')->where('school_id',$request['school_id'])->where('id','!=',$sender)->get();
            $user_sms=User::where('role','staff')->where('staff_role','teacher')->where('school_id',$request['school_id'])->where('id','!=',$sender)->get();

            $present = 'Dear Teacher , You has come to school on '.$request['date'];
            $absent = 'Dear Teacher , You remain absent on '.$request['date'];
            $holiday = 'Dear Teacher , there is holiday in our school on '.$request['date'];
            $emergency_close = 'Dear Teacher , school will be closed on '.$request['date'].' , All students  are not expected to report to school , All after school activities have been cancelled.';
           // $injured = '';
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
            $emergency_close = 'Dear Parents , school will be closed on '.$request['date'].' , All students  are not expected to report to school , All after school activities have been cancelled.';
            $vacation = 'Dear Parents school is remain close till '.$request['date'].' because of vacation. New semester will be started from '.Carbon::parse($request['date'] )->modify('+1 day')->format('Y-m-d');
        }
        else{
            $user_notification = new \Illuminate\Database\Eloquent\Collection;
            $user_sms = new \Illuminate\Database\Eloquent\Collection;
            $present = '';
            $absent = '';
            $holiday = '';
            $emergency_close = '';
            //$injured = '';
            $vacation = '';

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
                        $this->validate($request, [
                            'message' => 'required',
                        ]);
                        $msg=$request['message'];
                        $sid='WEBSMS';
                        $msg_type='';
                    }

                    if($request['sms_type'] == 'transactional' ){
                        $this->validate($request, [
                            'transactional_type' => 'required',
                            'date' => 'required',
                        ]);

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
                        if($request['transactional_type'] == 'injured'){
                            $msg='';
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
               // return '$finallist='.$final_list.'$sid='.$sid.'&msg='.$msg.'$msg_type='.$msg_type;
                    $ch = curl_init();// init curl
                    curl_setopt($ch, CURLOPT_URL,"http://bulksms.alayada.com/vendorsms/pushsms.aspx");
                    curl_setopt($ch, CURLOPT_POST, 1);// set post data to true
                    curl_setopt($ch, CURLOPT_POSTFIELDS,"user=chetanjsheth&password=C123123H1212&msisdn=".$final_list."&sid=".$sid."&msg=".$msg."&fl=0".$msg_type."");// post data
                    // receive server response ...
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);// gives you a response from the server

                    $response = curl_exec ($ch);// response it ouputed in the response var
                    curl_close ($ch);
                }

                if($type == 'notification'){

                    $this->validate($request, [
                        'message' => 'required',
                    ]); 

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
                                  $a=  PushNotification::setService('fcm')
                                        ->setMessage([
                                            'notification' => [
                                                'title' => Auth::user()->name.' message from School',
                                                'body' => $input['message'],
                                                'sound' => 'default'
                                            ],
                                            'data' => [
                                                'extraPayLoad1' => 'value1',
                                                'extraPayLoad2' => 'value2'
                                            ]
                                        ])
                                        ->setApiKey('AAAANfHw2Ls:APA91bEvYnZf_EHIqdrjyW-aPN-XpTZXt6bHeigfJATId9IKDCZqh6hLppD5CvN31yYCQPYzh5SYOK4ZscFF7rI7oR1TeS2w5cBpGZB0eGCWOsnw1MyyTx4os4F3jdKo_4opnXZrWxIY')
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

        \Session::flash('success', 'Notification sent successfully.');
        return redirect('notification');
    }
}
