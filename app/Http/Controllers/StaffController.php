<?php

namespace App\Http\Controllers;

use App\Staff;
use Illuminate\Http\Request;
use App\School;
use Carbon\Carbon;

use App\Http\Requests;
use Illuminate\Contracts\Validation\ValidationException;
use phpDocumentor\Reflection\File;
use phpDocumentor\Reflection\Types\Null_;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class StaffController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('staff');
        $this->middleware('role');

    }

    public function index(Request $request)
    {
        $data=[];
        $search='';
        $data['menu'] = "Staff";
//        if(Auth::user()->role == "staff") {
//            $data['staff'] = Staff::all()->where('role','staff')->where('school_id', Auth::user()->school_id);
//        } else {
//            $data['staff'] = Staff::all()->where('role','staff');
//        }
        
        if(Auth::user()->role == "staff") {
            if(!empty($request['search'])){
                $data['staff']  = Staff::where(function($query) use ($request)
                {
                    $query->where('role','staff')->where('school_id', Auth::user()->school_id);
                })
                    ->Where(function($query) use ($request)
                    {
                        $query->orWhere('name', 'like', '%'.$request['search'].'%')
                            ->orWhere('mobile', 'like', '%'.$request['search'].'%')
                            ->orWhere('email', 'like', '%'.$request['search'].'%')
                            ->orWhere('blood_group', 'like', '%'.$request['search'].'%');
                    })->Paginate($this->pagination);
                $search=$request['search'];
            }
            else{
                $data['staff'] = Staff::where('role','staff')->where('school_id', Auth::user()->school_id)->Paginate($this->pagination);
            }
            // $data['staff'] = Staff::all()->where('role','staff')->where('school_id', Auth::user()->school_id);
        }
        else {
            if(!empty($request['search'])){
                $data['staff']  = Staff::where(function($query) use ($request)
                {
                    $query->where('role','staff');
                })
                    ->Where(function($query) use ($request)
                    {
                        $query->orWhere('name', 'like', '%'.$request['search'].'%')
                            ->orWhere('mobile', 'like', '%'.$request['search'].'%')
                            ->orWhere('email', 'like', '%'.$request['search'].'%')
                            ->orWhere('blood_group', 'like', '%'.$request['search'].'%');
                    })->Paginate($this->pagination);

                $search=$request['search'];
            }
            else{
                $data['staff'] = Staff::where('role','staff')->Paginate($this->pagination);
            }
        }
        $data['search']=$search;
        return view('staff.details', $data);
    }

    public function create()
    {
        $data=[];
        $data['menu'] = "Staff";
        if(Auth::user()->role == "admin") {
            $data['name'] = School::lists('name','id')->all();
        }

        return view("staff.add",$data);
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $this->validate($request, [
            'name' => 'required',
            'mobile' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed',
            'password_confirmation' => 'required',
            'birthdate' => 'required',
            'staff_role' => 'required',
            'status' => 'required',
        ]);


        if(!empty($input->school_id))
        {
            $input->school_id=implode(',',$input->school_id);
        }

        $input['password'] = bcrypt($request['password']);
        $input['role'] = 'staff';

        Staff::create($input);

        \Session::flash('success','Staff has been inserted successfully!');
        return redirect('staff');
    }

    public function show($id)
    {
        $data=[];
        $data['menu'] = "Staff";
        $data['staff'] = Staff::with('staff_school')->findorFail($id);

        return view('staff.view',$data);
    }

    public function edit(Request $request,$id)
    {
        $data=[];
        $data['menu'] = "Staff";
        if(Auth::user()->role == "admin") {
            $data['name'] = School::lists('name','id')->all();
        }

        $data['staff'] = Staff::findorFail($id);
        $data['modes_selected'] = explode(",",$data['staff']['school_id']);
        return view('staff.edit',$data);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'mobile' => 'required',
            'email' => 'required|email|unique:users,email,'.$id.',id',
            'password' => 'confirmed',
            'birthdate' => 'required',
            'staff_role' => 'required',
            'status' => 'required',
        ]);

        //return $request;
        $input = $request->all();
        if(!empty($request['password'])){
            $input['password'] = bcrypt($request['password']);
        }
        else{
            unset($input['password']);
        }

        $staff = Staff::findorFail($id);

        if(!empty($input->school_id))
        {
            $input->school_id=implode(',',$input->school_id);
        }

        $input['role'] = 'staff';
        $staff->update($input);

        \Session::flash('success','Staff has been updated successfully!');
        return redirect('staff');
    }

    public function destroy($id)
    {
        $staff = Staff::findOrFail($id);
        $staff->delete();
        \Session::flash('danger','Staff has been deleted successfully!');
        return redirect('staff');
    }

    public function import_excel()
    {
        $data=[];
        $data['menu']="staff";
        return view('staff.import', $data);
    }

    public function import_data(Request $request)
    {

        $this->validate($request, [
            'staff_excel' => 'required',
        ]);

        try {

            $sheet1 = Excel::selectSheetsByIndex(0)->load($request->file('staff_excel'))->get();

            foreach($sheet1 as $row) {

                if (trim($row->staff_name) != '' && trim($row->staff_email) != '' && trim($row->staff_mobile_no) != '' && trim($row->staff_password) != '' && trim($row->staff_birthdate) != '' && trim($row->staff_role) != '') {

                    $school = School::where('name',trim($row->school))->first();

                    if (!empty($school)) {

                        $school_id = $school->id;

                        if(Auth::user()->role == "staff" && $school_id != Auth::user()->school_id) {
                            continue;
                        }

                        $birth_date = Carbon::parse($row->staff_birthdate)->format('Y-m-d');

                        $dup_staff = Staff::where('email',trim($row->staff_email))->where('school_id', $school_id)->where('role', 'staff')->first();

                        if(empty($dup_staff)) {

                            $staff = new Staff();

                            $staff->school_id = $school_id;
                            $staff->name = $row->staff_name;
                            $staff->email = $row->staff_email;
                            $staff->password = bcrypt($row->staff_password);
                            $staff->address = $row->staff_address;
                            $staff->birthdate = $birth_date;
                            $staff->blood_group = $row->staff_blood_group;
                            $staff->mobile = $row->staff_mobile_no;
                            $staff->school_time = $row->school_timing;
                            $staff->notes = $row->staff_notes;
                            $staff->rfid_no = $row->staff_rfid_no;
                            $staff->staff_role = $row->staff_role;
                            $staff->status = 'active';
                            $staff->role = 'staff';

                            $staff->save();

                        }
                    }
                }
            }

            \Session::flash('success', 'File Imported successfully.');
            return redirect('staff');
        } catch (\Exception $e) {
            \Session::flash('danger', $e->getMessage());
            return redirect('staff/import');
        }
    }

    public function export()
    {

        if(Auth::user()->role == "staff") {
            $data = Staff::select('name as Name','email as Email','address as Address','birthdate as BirthDate','blood_group as BloodGroup','mobile as Mobile','school_time as SchoolTime','notes as Notes','rfid_no as RFID','school_id')->where('role','staff')->where('school_id', Auth::user()->school_id)->get()->toArray();

        } else {
            $data = Staff::select('name as Name','email as Email','address as Address','birthdate as BirthDate','blood_group as BloodGroup','mobile as Mobile','school_time as SchoolTime','notes as Notes','rfid_no as RFID','school_id')->where('role','staff')->get()->toArray();

        }


        foreach($data as $key => $value) {
            $school_name = School::select('name')->where('id', $value['school_id'])->first();
            $data[$key]['School'] = $school_name->name;
            unset($data[$key]['school_id']);
        }

        return Excel::create('Staff', function($excel) use ($data) {

            $excel->sheet('staff_persons', function($sheet) use ($data) {
                $sheet->fromArray($data);
            });

        })->download('xlsx');

    }

}
