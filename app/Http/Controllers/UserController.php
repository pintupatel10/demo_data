<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

class UserController extends Controller
{
    public function __construct(\Illuminate\Http\Request $request)
    {
        //echo $uri = $request->path();
        $this->middleware('auth');
        $this->middleware('regional');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($name)
    {
        $data=[];
        $data['menu']="User";
        $data['role'] = $name;
        $data['list'] = User::where('role',$name)->get();
        return view('users.details', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($name)
    {
        $data=[];
        $data['menu']="User";
        $data['role'] = $name;
        return view("users.add",$data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request,$name)
    {
        $this->validate($request, [
            'name' => 'required',
            //'email' => 'required|email|unique:users,email',
            'email' => 'required|email|unique:users,email,NULL,id,deleted_at,NULL',
            'password' => 'required|confirmed',
            'password_confirmation' => 'required',
            //'role'=>'required',
            //'status' => 'required',
        ]);

        $request['role']=$name;
        $request['password'] = bcrypt($request['password']);
        $request['status'] = 'active';
        $user = new User($request->all());
        $user->save();
        \Session::flash('success', 'User has been inserted successfully!');
        return redirect($name.'/users');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($name,$id)
    {
        $data=[];
        $data['role'] = $name;
        $data['menu']="User";
        $data['user'] = User::findorFail($id);
        return view('users.view',$data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
       // return "in";
        $data=[];
        $data['role'] = "admin";
        $data['menu']="User";
        $data['user'] = User::findorFail($id);
        return view('users.edit',$data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($name,Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$id.',id,deleted_at,NULL',
            'password' => 'confirmed',
            'current_password' => 'required_with:password',
            //'role'=>'required',
            //'status' => 'required',
        ]);
        //return $request;
        if(!empty($request['password'])){
            $request['password'] = bcrypt($request['password']);
        }
        else{
            unset($request['password']);
        }
        $request['status'] = 'active';
        $role = $name;

        $user = User::findorFail($id);
/*
        if(!empty($request['password'])){
        	if (!\Hash::check($request->input('current_password'), $user->password)) {
	        	return back()->withInput()->withErrors(['current_password' => 'Current password does not match our record']);
        	}
        }
*/
        $user->update($request->all());
        \Session::flash('success','User has been updated successfully!');
        //return redirect($role.'/users');
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($name,$id)
    {
        $user = \App\User::findOrFail($id);
        $user->delete();
        $role = $name;
        \Session::flash('danger','User has been deleted successfully!');
        return redirect($role.'/users');
    }

    public function assign(Request $request)
    {
        $user = User::findorFail($request['id']);
        $user['status']="active";
        $user->update($request->all());
        return $request['id'];
    }

    public function unassign(Request $request)
    {
        $user = User::findorFail($request['id']);
        $user['status']="inactive";
        $user->update($request->all());
        return $request['id'];
    }
}
