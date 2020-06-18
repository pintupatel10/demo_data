<?php

namespace App\Http\Controllers;

use App\Class_Master;
use App\Division;
use App\School;
use Illuminate\Http\Request;

use App\Http\Requests;

class DivisionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role');
    }

    public function index()
    {
        $data=[];
        $data['menu'] = "Division";
        $data['division'] = Division::with('school')->with('classm')->get();
        return view('division.details', $data);
    }

    public function create()
    {
        $data=[];
        $data['menu']="Division";
        $data['name'] = School::lists('name','id')->all();
        $data['name1'] = []; //Class_Master::lists('name','id')->all();
        return view("division.add",$data);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'school_id' => 'required',
            'class_id' => 'required',
            'division' => 'required',
            'status' => 'required',
        ]);

        $input = $request->all();
        Division::create($input);

        \Session::flash('success','Division has been inserted successfully!');
        return redirect('division');
    }

    public function show($id)
    {
        $data=[];
        $data['menu']="Division";
        $data['division'] = Division::findorFail($id);
        return view('division.view',$data);
    }

    public function edit(Request $request,$id)
    {
        $data=[];
        $data['menu'] = "Division";
        $data['name']=School::lists('name','id')->all();
        $data['name1'] = Class_Master::lists('name','id')->all();
        $data['division'] = Division::findorFail($id);
        $data['modes_selected'] = explode(",",$data['division']['school_id']);
        $data['modes'] = explode(",",$data['division']['class_id']);
        $data['modes1'] = explode(",",$data['division']['division']);
        return view('division.edit',$data);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'status' => 'required',
        ]);

        $division = Division::findorFail($id);
        $input = $request->all();

        if(!empty($input->school_id))
        {
            $input->school_id=implode(',',$input->school_id);
        }


        if(!empty($input->class_id))
        {
            $input->class_id=implode(',',$input->class_id);
        }

        if(!empty($input->division))
        {
            $input->division=implode(',',$input->division);
        }

        $division->update($input);

        \Session::flash('success','Division has been updated successfully!');
        return redirect('division');
    }

    public function destroy($id)
    {
        $division = Division::findOrFail($id);
        $division->delete();
        \Session::flash('danger','Division has been deleted successfully!');
        return redirect('division');
    }
    public function product_line($id)
    {

        $division = Division::where('type_id',$id)->get();
        foreach ($division as $division1){
            echo '<option value="'.$division1['id'].'">'.$division1['name'].'</option>';
        }

        return "";
    }
}
