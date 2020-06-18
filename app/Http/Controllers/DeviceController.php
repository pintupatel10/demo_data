<?php

namespace App\Http\Controllers;

use App\School;
use App\Device;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Auth;


class DeviceController extends Controller
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
        $data['menu'] = "Device";
        $data['device'] = Device::all();
        return view('device.details', $data);
    }

    public function create()
    {
        $data=[];
        $data['menu'] = "Device";

        if(Auth::user()->role == "admin") {
            $data['name'] = School::lists('name','id')->all();
        }

        return view("device.add",$data);
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $this->validate($request, [
            'name' => 'required',
            'serial_no' => 'required',
            'status' => 'required',
        ]);

        Device::create($input);
        \Session::flash('success','Device has been inserted successfully!');
        return redirect('device');
    }

    public function show($id)
    {
        $data=[];
        $data['menu'] = "Device";
        $data['device'] = Device::findorFail($id);

        return view('device.view',$data);
    }

    public function edit(Request $request,$id)
    {
        $data=[];
        $data['menu'] = "Device";
        $data['device'] = Device::findorFail($id);

        if(Auth::user()->role == "admin") {
            $data['name'] = School::lists('name','id')->all();
        }

        return view('device.edit',$data);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'serial_no' => 'required',
            'status' => 'required',
        ]);

        $device = Device::findorFail($id);
        $input = $request->all();

        $device->update($input);
        \Session::flash('success','Device has been updated successfully!');
        return redirect('device');
    }

    public function destroy($id)
    {
        $device = Device::findOrFail($id);
        $device->delete();
        \Session::flash('danger','Device has been deleted successfully!');
        return redirect('device');
    }
}
