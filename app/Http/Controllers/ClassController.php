<?php

namespace App\Http\Controllers;

use App\Class_Master;
use App\School;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Auth;

class ClassController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('staff');
    }

    public function index()
    {
        $data=[];
        $data['menu'] = "Class";
        if(Auth::user()->role == "staff") {
            $data['class'] = Class_Master::with('school')->where('school_id', Auth::user()->school_id)->get();
        } else {
            $data['class'] = Class_Master::with('school')->get();
        }

        return view('class.details', $data);
    }

    public function create()
    {
        $data=[];
        $data['menu']="Class";
        if(Auth::user()->role == "admin") {
            $data['name'] = School::lists('name', 'id')->all();
        }
        return view("class.add",$data);
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $this->validate($request, [
            'name' => 'required',
            'status' => 'required',
        ]);


        if(!empty($input->school_id))
        {
            $input->school_id=implode(',',$input->school_id);
        }

        Class_Master::create($input);

        \Session::flash('success','Class has been inserted successfully!');

        return redirect('class');
    }

    public function show($id)
    {
        $data=[];
        $data['menu']="Class";
        $data['class'] = Class_Master::findorFail($id);
        return view('class.view',$data);
    }

    public function edit(Request $request,$id)
    {
        $data=[];
        $data['menu'] = "Class";
        if(Auth::user()->role == "admin") {
            $data['name'] = School::lists('name','id')->all();
        }

        $data['class'] = Class_Master::findorFail($id);
        $data['modes_selected'] = explode(",",$data['class']['school_id']);
        return view('class.edit',$data);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'status' => 'required',
        ]);

        $class = Class_Master::findorFail($id);
        $input = $request->all();
        if(!empty($input->school_id))
        {
            $input->school_id=implode(',',$input->school_id);
        }
        $class->update($input);

        \Session::flash('success','Class has been updated successfully!');
        return redirect('class');
    }

    public function destroy($id)
    {
        $class = Class_Master::findOrFail($id);
        $class->delete();

        \Session::flash('danger','Class has been deleted successfully!');
        return redirect('class');
    }

    public function get_classes($id)
    {
        $classes = Class_Master::where('school_id',$id)->get();
        foreach ($classes as $class1){
            echo '<option value="'.$class1['id'].'">'.$class1['name'].'</option>';
        }

        return "";
    }

}
