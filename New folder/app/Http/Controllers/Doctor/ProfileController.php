<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
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

        $result = \Api::sendRequest("GET", "/doctor/{$user->id}/profile");

        if (!empty($result["data"]) && $result["code"] == 200) {
            $profile = $result["data"]["profile"];
            $country = $result["data"]["country"];
            return view("doctor.dashboard.profile", compact('profile', 'country'));
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

}
