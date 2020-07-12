<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoginController extends Controller
{

    public function __construct()
    {
        //parent::__construct();
    }

    /**
     * Site login view
     *
     */

    public function index(Request $request)
    {
        return view("login");
    }

    public function logout(Request $request)
    {
        \Auth::logout();

        return redirect(route("site.home"));
    }

    public function login(Request $request)
    {
        $params = $request->all();
        if (\Auth::attempt(['email' => $params['email'], 'password' => $params['password']], true)) {
            $user = \Auth::user();
            if (!empty($user)) {
                if ($user->role_id == 1) {
                    return redirect(route("patient.profile"));
                } elseif ($user->role_id == 2) {
                    return redirect(route("doctor.profile"));
                }
            }
        }

        return redirect()->back();
    }

}
