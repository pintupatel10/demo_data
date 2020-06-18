<?php

namespace App\Http\Controllers;

use App\Class_Master;
use App\Division;
use App\Parents;
use App\ParentChild;
use App\School;
use App\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Contracts\Validation\ValidationException;
use phpDocumentor\Reflection\File;
use phpDocumentor\Reflection\Types\Null_;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('staff');
    }

    public function index(Request $request)
    {
        $data=[];
        $search='';
        $data['menu'] = "Student";
        if(Auth::user()->role == "staff") {
            if(!empty($request['search'])){
                $data['student']  = Student::where(function($query) use ($request)
                {
                    $query->where('role','student')->where('school_id', Auth::user()->school_id);
                })
                    ->Where(function($query) use ($request)
                    {
                        $query->orWhere('name', 'like', '%'.$request['search'].'%')
                            ->orWhere('email', 'like', '%'.$request['search'].'%')
                            ->orWhere('parents_name', 'like', '%'.$request['search'].'%')
                            ->orWhere('blood_group', 'like', '%'.$request['search'].'%');
                    })->Paginate($this->pagination);
                $search=$request['search'];
            }
            else{
                $data['student'] = Student::where('role','student')->where('school_id', Auth::user()->school_id)->Paginate($this->pagination);
            }
           // $data['student'] = Student::all()->where('role','student')->where('school_id', Auth::user()->school_id);
        }
        else {
            if(!empty($request['search'])){
                $data['student']  = Student::where(function($query) use ($request)
                {
                    $query->where('role','student');
                })
                    ->Where(function($query) use ($request)
                    {
                        $query->orWhere('name', 'like', '%'.$request['search'].'%')
                            ->orWhere('email', 'like', '%'.$request['search'].'%')
                            ->orWhere('parents_name', 'like', '%'.$request['search'].'%')
                            ->orWhere('blood_group', 'like', '%'.$request['search'].'%');
                    })->Paginate($this->pagination);

                $search=$request['search'];
            }
            else{
                $data['student'] = Student::where('role','student')->Paginate($this->pagination);
            }

        }

        $data['search']=$search;
        return view('student.details', $data);
    }

    public function create()
    {
        $data=[];
        $data['menu'] = "Student";
        if(Auth::user()->role == "staff") {
            // $data['name1'] = Class_Master::lists('name','id')->all();
            $data['name1'] = Class_Master::where('school_id', Auth::user()->school_id)->lists('name','id')->toArray();

        } else {
            $data['name'] = School::lists('name','id')->all();
            $data['name1'] = Class_Master::lists('name','id')->all();
        }

        return view("student.add",$data);
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $this->validate($request, [
            'roll_no'=>'required',
            'class_id' => 'required',
            'division' => 'required',
            'name' => 'required',
            'birthdate' => 'required',
            'status' => 'required',
        ]);

        if(!empty($input->school_id))
        {
            $input->school_id=implode(',',$input->school_id);
        }

        if(!empty($input->class_id))
        {
            $input->class_id=implode(',',$input->class_id);
        }

        $input['role'] = 'student';

        Student::create($input);

        $check_division=Division::where('school_id',$request['school_id'])->where('class_id',$request['class_id'])->where('division',$request['division'])->first();
        if(!empty($check_division)){
         $in['school_id']=$request['school_id'];
         $in['class_id']=$request['class_id'];
         $in['division']=$request['division'];
         $in['status']='active';
         $div=Division::create($in);
        }

        \Session::flash('success','Student has been inserted successfully!');
        return redirect('student');
    }

    public function show($id)
    {
        $data=[];
        $data['menu']="Student";
        $data['student'] = Student::with('student_school')->with('student_class')->findorFail($id);

        return view('student.view',$data);
    }

    public function edit(Request $request,$id)
    {
        $data=[];
        $data['menu'] = "Student";

        if(Auth::user()->role == "staff") {
            $data['name1'] = Class_Master::where('school_id', Auth::user()->school_id)->lists('name','id')->toArray();

        } else {
            $data['name'] = School::lists('name','id')->all();
            $data['name1'] = Class_Master::lists('name','id')->all();
        }

        $data['student'] = Student::findorFail($id);
        $data['modes_selected'] = explode(",",$data['student']['school_id']);
        $data['modes'] = explode(",",$data['student']['class_id']);
        return view('student.edit',$data);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'roll_no'=>'required',
            'class_id' => 'required',
            'division' => 'required',
            'name' => 'required',
            'birthdate' => 'required',
            'status' => 'required',
        ]);

        $student = Student::findorFail($id);
        $input = $request->all();

        if(!empty($input->school_id))
        {
            $input->school_id=implode(',',$input->school_id);
        }

        if(!empty($input->class_id))
        {
            $input->class_id=implode(',',$input->class_id);
        }

        $input['role'] = 'student';
        $student->update($input);

        $check_division=Division::where('school_id',$request['school_id'])->where('class_id',$request['class_id'])->where('division',$request['division'])->first();

        if(!empty($check_division)){
            $in['school_id']=$request['school_id'];
            $in['class_id']=$request['class_id'];
            $in['division']=$request['division'];
            $in['status']='active';
            $div=Division::create($in);
        }

        \Session::flash('success','Student has been updated successfully!');
        return redirect('student');
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();
        \Session::flash('danger','Student has been deleted successfully!');
        return redirect('student');
    }

    public function school_class($id)
    {
        $options = '<option value="">Please Select</option>';
        $types = Class_Master::where('school_id',$id)->get();
        foreach ($types as $type){
            $options .= '<option value="'.$type['id'].'">'.$type['name'].'</option>';
        }

        echo $options;
        return '';

    }

    public function import_excel()
    {
        $data=[];
        $data['menu']="student";
        return view('student.import', $data);
    }

    public function import_data(Request $request)
    {

        $this->validate($request, [
            'student_excel' => 'required',
        ]);

        try {

            $sheet1 = Excel::selectSheetsByIndex(0)->load($request->file('student_excel'))->get();

            foreach($sheet1 as $row) {

                if (trim($row->student_name) != '' && trim($row->student_birthdate) != '') {

                    $school = School::where('name',trim($row->school))->first();

                    if (!empty($school)) {

                        $school_id = $school->id;

                        if(Auth::user()->role == "staff" && $school_id != Auth::user()->school_id) {
                            continue;
                        }

                        $sc_class = Class_Master::firstOrCreate(['school_id' => $school_id, 'name' => trim($row->student_class)]);

                        $class_id = $sc_class->id;

                        Division::firstOrCreate(['school_id' => $school_id, 'class_id' => $class_id, 'division' => strtoupper(trim($row->student_division))]);

                        $birth_date = Carbon::parse($row->student_birthdate)->format('Y-m-d');

                        $parents = Parents::where('name',trim($row->student_parents_name))->where('school_id',$school_id)->where('role','parent')->first();

                        $parent_id = 0;
                        if(!empty($parents)) {
                            $parent_id = $parents->id;
                        }

                        $student = new Student();

                        $student->school_id = $school_id;
                        $student->class_id = $class_id;
                        $student->division = $row->student_division;
                        $student->name = $row->student_name;
                        $student->address = $row->student_address;
                        $student->birthdate = $birth_date;
                        $student->blood_group = $row->student_blood_group;
                        $student->mobile = $row->student_parents_mobile_no;
                        $student->school_time = $row->school_timing;
                        $student->parents_name = $row->student_parents_name;
                        $student->notes = $row->student_notes;
                        $student->rfid_no = $row->student_rfid_no;
                        $student->status = 'active';
                        $student->role = 'student';

                        $student->save();

                        $student_id = $student->id;

                        if($parent_id > 0 && $student_id > 0) {
                            $now = new \DateTime();
                            $data[] = array('parent_id' => $parent_id, 'student_id' => $student_id, 'created_at' => $now, 'updated_at' => $now);

                            $parentChild = new ParentChild();
                            $parentChild->insert($data);
                        }
                    }
                }
            }

            \Session::flash('success', 'File Imported successfully.');
            return redirect('student');
        } catch (\Exception $e) {
            \Session::flash('danger', $e->getMessage());
            return redirect('student/import');
        }
    }

    public function export()
    {

        if(Auth::user()->role == "staff") {
            $data = Student::select('name as Name','address as Address','birthdate as BirthDate','blood_group as BloodGroup','mobile as ParentMobile', 'parents_name as ParentName','school_time as SchoolTime','notes as Notes','rfid_no as RFID','school_id','class_id','division as Division')->where('role','student')->where('school_id', Auth::user()->school_id)->get()->toArray();

        } else {
            $data = Student::select('name as Name','address as Address','birthdate as BirthDate','blood_group as BloodGroup','mobile as ParentMobile', 'parents_name as ParentName','school_time as SchoolTime','notes as Notes','rfid_no as RFID','school_id','class_id','division as Division')->where('role','student')->get()->toArray();

        }

        foreach($data as $key => $value) {
            $school_name = School::select('name')->where('id', $value['school_id'])->first();
            $data[$key]['School'] = $school_name->name;
            $class_name = Class_Master::select('name')->where('id', $value['class_id'])->first();
            $data[$key]['Class'] = $class_name->name;
            unset($data[$key]['school_id']);
            unset($data[$key]['class_id']);
        }

        return Excel::create('Student', function($excel) use ($data) {

            $excel->sheet('students', function($sheet) use ($data) {
                $sheet->fromArray($data);
            });

        })->download('xlsx');

    }
}
