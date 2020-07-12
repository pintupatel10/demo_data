<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{

    public function __construct()
    {
        //parent::__construct();
    }

    public function index(Request $request)
    {
        $user = \Auth::user();

        if($user->role_id != 2) {
            abort(404);
        }

        $result = \Api::sendRequest("GET", "/doctor/{$user->id}/schedule");

    //echo '<pre>'; print_r($result); echo '</pre>';exit;

        if (!empty($result["data"]) && $result["code"] == 200) {
            $weekDays = $result["data"]['week_day'];    
            $schedule = $result["data"]['schedule'];    
            $profile = $result["data"]['profile'];    
            return view("doctor.dashboard.schedule", compact('weekDays', 'schedule', 'profile'));
        }

    }

    public function save(Request $request, $id)
    {
        $user   = \Auth::user();
        $id     = $user->id;
        $params = $request->all();

        // fix for profile image
        if ($request->hasFile('profile_pic')) {
            $profilePic = 'profile_pic_' . time() . '.' . $request->file('profile_pic')->getClientOriginalExtension();
            $request->file('profile_pic')->move(
                base_path() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'profile_pic' . DIRECTORY_SEPARATOR, $profilePic
            );
            $params['profile_pic'] = fopen(base_path() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'profile_pic' . DIRECTORY_SEPARATOR . $profilePic, 'r');
        }

        $postData = [];
        foreach ($params as $key => $pData) {
            $postData[] = [
                'name'     => $key,
                'contents' => is_array($pData) ? json_encode($pData) : $pData,
            ];
        }

        $result = \Api::sendWithFileRequest("POST", "/doctor/{$id}/profile", $postData);
        return response()->json($result);
    }

    public function saveTimeSloats(Request $request)
    {
        $user                  = \Auth::user();
        $params                = $request->all();
        $params['doctor_id']   = $user->id;

        $postData = [];
        foreach ($params as $key => $pData) {
            $postData[] = [
                'name'     => $key,
                'contents' => is_array($pData) ? json_encode($pData) : $pData,
            ];
        }
        
        $result = \Api::sendWithFileRequest("POST", "/doctor/schedule/time-slots-add", $postData);
        return response()->json($result);   
    }

}
