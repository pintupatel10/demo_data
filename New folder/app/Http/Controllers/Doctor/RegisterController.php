<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Library\Api;

class RegisterController extends Controller
{

	public function __construct()
	{
		//parent::__construct();
	}

	public function index(Request $request)
	{
		return view("doctor.register");
	}

	public function register(Request $request)
    {
        $result = Api::sendRequest("POST", "/doctor/register", $request->all());

        // check user registration done success
        if($result["code"] == 200) {
            if (\Auth::attempt(['email' => $request->input('email'), 'password' => $request->input('password')], true)) {
                $result["_r"] = route("doctor.profile");
            }    
        }
 
        return response()->json($result);
    }


}
