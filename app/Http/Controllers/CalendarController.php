<?php

namespace App\Http\Controllers;

use App\Calendar;
use App\School;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('staff');
        $this->middleware('principal');
    }

    public function index(Request $request)
    {
        $data=[];
        $search='';

        $data['menu'] = "Calendar";
//        if(Auth::user()->role == "staff") {
//            $data['calendar'] = Calendar::with('calendar_school')->where('school_id', Auth::user()->school_id)->Paginate($this->pagination);
//        } else {
//            $data['calendar'] = Calendar::with('calendar_school')->Paginate($this->pagination);
//        }

        if(Auth::user()->role == "staff") {
            if(!empty($request['search'])){
                $data['calendar'] = Calendar::with('calendar_school')->where(function($query) use ($request)
                {
                    $query->where('school_id', Auth::user()->school_id);
                })
                    ->Where(function($query) use ($request)
                    {
                        $query->orWhere('holiday_name', 'like', '%'.$request['search'].'%')
                            ->orWhere('holiday_date', 'like', '%'.$request['search'].'%')
                            ->orWhere('status', 'like', '%'.$request['search'].'%');
                    })->Paginate($this->pagination);
                $search=$request['search'];
            }
            else{
                $data['calendar'] = Calendar::with('calendar_school')->where('school_id', Auth::user()->school_id)->Paginate($this->pagination);
            }
            // $data['student'] = Student::all()->where('role','student')->where('school_id', Auth::user()->school_id);
        }
        else {
            if(!empty($request['search'])){
                $data['calendar'] = Calendar::with('calendar_school')->orWhere('holiday_name', 'like', '%'.$request['search'].'%')
                    ->orWhere('holiday_date', 'like', '%'.$request['search'].'%')
                    ->orWhere('status', 'like', '%'.$request['search'].'%')->Paginate($this->pagination);

                $search=$request['search'];
            }
            else{
                $data['calendar'] = Calendar::with('calendar_school')->Paginate($this->pagination);
            }

        }

        $data['search']=$search;
        return view('calendar.details', $data);
    }

    public function create()
    {
        $data=[];
        $data['menu'] = "Calendar";
        if(Auth::user()->role == "admin") {
            $data['name'] = School::lists('name','id')->all();
        }

        return view("calendar.add",$data);
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $this->validate($request, [
            'school_id' => 'required',
            'holiday_name' => 'required',
            'holiday_date' => 'required',
            'status' => 'required',
        ]);


        if(!empty($input->school_id))
        {
            $input->school_id=implode(',',$input->school_id);
        }

        Calendar::create($input);
        \Session::flash('success','Calendar Holiday has been inserted successfully!');

        return redirect('calendar');
    }

    public function show($id)
    {
        $data=[];
        $data['menu'] = "Calendar";
        $data['calendar'] = Calendar::with('calendar_school')->findorFail($id);
        return view('calendar.view',$data);
    }

    public function edit(Request $request,$id)
    {
        $data=[];
        $data['menu']  = "Calendar";

        $data['calendar'] = Calendar::findorFail($id);

        if(Auth::user()->role == "admin") {
            $data['name'] = School::lists('name','id')->all();
            $data['modes_selected'] = explode(",",$data['calendar']['school_id']);
        }

        return view('calendar.edit',$data);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'school_id' => 'required',
            'holiday_name' => 'required',
            'holiday_date' => 'required',
            'status' => 'required',
        ]);

        $calendar = Calendar::findorFail($id);
        $input = $request->all();

        if(!empty($input->school_id))
        {
            $input->school_id = implode(',',$input->school_id);
        }

        $calendar->update($input);

        \Session::flash('success','Calendar Holiday has been updated successfully!');
        return redirect('calendar');
    }

    public function destroy($id)
    {
        $calendar = Calendar::findOrFail($id);
        $calendar->delete();

        \Session::flash('danger','Calendar Holiday has been deleted successfully!');
        return redirect('calendar');
    }
}
