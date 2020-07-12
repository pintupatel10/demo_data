<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Library\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{

    public function __construct()
    {
        //parent::__construct();
    }

    public function index(Request $request)
    {
        return view("patient.register");
    }

    public function register(Request $request)
    {
        $result = Api::sendRequest("POST", "/patient/register", $request->all());

        // check user registration done success
        if($result["code"] == 200) {
            if (Auth::attempt(['email' => $request->input('email'), 'password' => $request->input('password')], true)) {
                $result["_r"] = route("patient.profile");
            }    
        }
 
        return response()->json($result);
    }

}
