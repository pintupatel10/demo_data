<?php

namespace App\Http\Controllers;

use App\Calendar;
use App\School;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;

class SchoolController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role');
    }

    public function index()
    {
        $data=[];
        $data['menu'] = "School";
        $data['list'] = School::all();
        return view('school.details', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data=[];
        $data['menu']="School";
        return view("school.add",$data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $this->validate($request, [
            'name' => 'required',
            'medium' => 'required',
            'type' => 'required',
            'phone' => 'required',
            'mobile' => 'required',
            'website' => 'required',
            'status' => 'required',
        ]);

        if($photo = $request->file('image'))
        {
            $root = base_path() . '/public/resource/school/' ;
            $name = str_random(20).".".$photo->getClientOriginalExtension();
            if (!file_exists($root)) {
                mkdir($root, 0777, true);
            }
            $image_path = "resource/school/".$name;
            $photo->move($root,$name);
            $input['image'] = $image_path;
        }

       $school= School::create($input);
        $start_date=Carbon::today()->format('Y-m-d');
        $start_time = strtotime($start_date);

         $end_date = date('Y-m-d', strtotime('+2 years'));
         $end_time = strtotime($end_date);

        for ($i = $start_time; $i < $end_time; $i += 86400) {
            if (date('N', $i) == 7 || date('N', $i) == 6) {
                $date= date('Y-m-d', $i);
                $input1['holiday_name']=date("l",$i);
                $input1['school_id']=$school->id;
                $input1['holiday_date']=$date;
                $input1['status']='active';
                $cal=Calendar::create($input1);
            }
        }
        \Session::flash('success','School has been inserted successfully!');
        return redirect('school');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data=[];
        $data['menu']="School";
        $data['school'] = School::findorFail($id);
        return view('school.view',$data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request,$id)
    {
        $data=[];
        $data['menu'] = "School";
        $data['school'] = School::findorFail($id);
        return view('school.edit',$data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'medium' => 'required',
            'type' => 'required',
            'phone' => 'required',
            'mobile' => 'required',
            'website' => 'required',
            'status' => 'required',
        ]);

        $school = School::findorFail($id);
        $input = $request->all();
        if($photo = $request->file('image'))
        {
            $root = base_path() . '/public/resource/school/';
            $name = str_random(20).".".$photo->getClientOriginalExtension();
            $mimetype = $photo->getMimeType();
            $explode = explode("/",$mimetype);
            $type = $explode[0];
            if (!file_exists($root)) {
                mkdir($root, 0777, true);
            }
            $photo->move($root,$name);
            $input['image'] = "resource/school/".$name;
        }
        $school->update($input);
        \Session::flash('success','School has been updated successfully!');
        return redirect('school');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $school = School::findOrFail($id);
        $school->delete();
        \Session::flash('danger','School has been deleted successfully!');
        return redirect('school');
    }
}
