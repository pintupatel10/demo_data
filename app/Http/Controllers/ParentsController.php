<?php

namespace App\Http\Controllers;

use App\ParentChild;
use App\Parents;
use App\Student;
use App\User;
use Illuminate\Http\Request;
use App\School;
use App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationException;
use phpDocumentor\Reflection\File;
use phpDocumentor\Reflection\Types\Null_;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ParentsController extends Controller
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
        $data['menu'] = "Parents";
//        if(Auth::user()->role == "staff") {
//            $data['parents'] = Parents::all()->where('role','parent')->where('school_id', Auth::user()->school_id);
//        } else {
//            $data['parents'] = Parents::all()->where('role','parent');
//        }

        if(Auth::user()->role == "staff") {
            if(!empty($request['search'])){
                $data['parents'] = Parents::where(function($query) use ($request)
                {
                    $query->where('role','parent')->where('school_id', Auth::user()->school_id);
                })
                    ->Where(function($query) use ($request)
                    {
                        $query->orWhere('name', 'like', '%'.$request['search'].'%')
                            ->orWhere('email', 'like', '%'.$request['search'].'%')
                            ->orWhere('mobile', 'like', '%'.$request['search'].'%')
                            ->orWhere('status', 'like', '%'.$request['search'].'%');
                    })->Paginate($this->pagination);
                $search=$request['search'];
            }
            else{
                $data['parents'] = Parents::where('role','parent')->where('school_id', Auth::user()->school_id)->Paginate($this->pagination);
            }
            // $data['student'] = Student::all()->where('role','student')->where('school_id', Auth::user()->school_id);
        }
        else {
            if(!empty($request['search'])){
                $data['parents'] = Parents::where(function($query) use ($request)
                {
                    $query->where('role','parent');
                })
                    ->Where(function($query) use ($request)
                    {
                        $query->orWhere('name', 'like', '%'.$request['search'].'%')
                            ->orWhere('email', 'like', '%'.$request['search'].'%')
                            ->orWhere('mobile', 'like', '%'.$request['search'].'%')
                            ->orWhere('status', 'like', '%'.$request['search'].'%');    
                    })->Paginate($this->pagination);

                $search=$request['search'];
             //   return $data['parents'];
            }
            else{
                $data['parents'] = Parents::where('role','parent')->Paginate($this->pagination);
            }

        }
        $data['search']=$search;

        return view('parents.details', $data);
    }

    public function create()
    {
        $data=[];
        $data['menu'] = "Parents";
        if(Auth::user()->role == "staff") {
            $data['students'] = Student::where('role','student')->where('school_id', Auth::user()->school_id)->lists('name','id');

        } else {
            $data['name'] = School::lists('name','id')->all();
            $data['students'] = Student::where('role','student')->lists('name','id');
        }

        return view("parents.add",$data);
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
            'status' => 'required',
        ]);

        $input['role'] = 'parent';

        $parent = new Parents();
        $parent->name = $input['name'];
        //$parent->school_id = $input['school_id'];
        $parent->email = $input['email'];
        $parent->password = bcrypt($input['password']);
        $parent->mobile = $input['mobile'];
        $parent->role = 'parent';
        $parent->status = $input['status'];
        $parent->save();

        $now = new \DateTime();
        $parent_id = $parent->id;

        if(isset($request['student_id'])) {
            foreach($input['student_id'] as $sid) {
                $data[] = array('parent_id' => $parent_id, 'student_id' => $sid, 'created_at' => $now, 'updated_at' => $now);
                /*update mobile no and parents name in student*/
                $student=Student::where('role','student')->find($sid);
                if(!empty($student)) {
                    $input1['parents_name'] = $input['name'];
                    $input1['mobile'] = $input['mobile'];
                    $student->update($input1);
                }
            }
            $parentChild = new ParentChild;
            $parentChild->insert($data);


        }

        \Session::flash('success','Parent has been inserted successfully!');
        return redirect('parents');
    }

    public function show($id)
    {
        $data=[];
        $data['menu'] = "Parents";
        $data['parents'] = Parents::with('parents_school')->findorFail($id);

        return view('parents.view',$data);
    }


    public function edit(Request $request,$id)
    {
        $data=[];
        $data['menu'] = "Parents";
        $student= new \Illuminate\Database\Eloquent\Collection;
        $data['students_selected'] = ParentChild::where('parent_id', $id)->lists('student_id')->toArray();
        foreach ($data['students_selected'] as $key=> $val){
            $st=User::select('id','name')->where('role','student')->where('id',$val)->first();
             $student->push($st);
        }
        $data['student']=$student;
        $data['parents'] = Parents::findorFail($id);
        return view('parents.edit',$data);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'mobile' => 'required',
            'email' => 'required|email|unique:users,email,'.$id.',id',
            'password' => 'confirmed',
            'status' => 'required',
        ]);

        $parent = Parents::findorFail($id);
       return  $input = $request->all();

        if(!empty($request['password'])){
            $parent->password = bcrypt($request['password']);
        }

        $parent->name = $input['name'];
       // $parent->school_id = $input['school_id'];
        $parent->email = $input['email'];
        $parent->mobile = $input['mobile'];
        $parent->role = 'parent';
        $parent->status = $input['status'];
        $parent->update();

        $now = new \DateTime();
        $parent_id = $parent->id;

        $delete_parent_child = ParentChild::where('parent_id',$parent_id);
        $delete_parent_child->forceDelete();

        if(isset($input['student_id'])) {

            foreach ($input['student_id'] as $sid) {
                $data[] = array('parent_id' => $parent_id, 'student_id' => $sid, 'created_at' => $now, 'updated_at' => $now);

                /*update mobile no and parents name in student*/
                $student=Student::where('role','student')->find($sid);
                if(!empty($student)){
                    $input1['parents_name'] = $input['name'];
                    $input1['mobile'] = $input['mobile'];
                    $student->update($input1);
                }
            }
            $parentChild = new ParentChild;
            $parentChild->insert($data);
        }

        \Session::flash('success','Parent has been updated successfully!');
        return redirect('parents');
    }

    public function destroy($id)
    {
        $parent = Parents::findOrFail($id);
        $parent->delete();
        \Session::flash('danger','Parent has been deleted successfully!');
        return redirect('parents');
    }

    public function school_students($school_id) {

        $students = User::where('role','student')->where('school_id',$school_id)->get();

        foreach ($students as $student){
            echo '<option value="'.$student['id'].'">'.$student['name'].'</option>';
        }
        return "";
    }


    public function get_students($str,$selectedvalues){

        $selected_student=explode(',',$selectedvalues);
           // $queryString = str_replace("'","&#39;",stripslashes($str));
            if(strlen($str) >0)
            {
                if(Auth::user()->role == 'admin') {
                    $user = User::select('id', 'name')->where('role', 'student')->Where('name', 'like', '%' . $str . '%')->whereNotIn('id', $selected_student)->get();
                }
                else{
                    $user = User::select('id', 'name')->where('role', 'student')->Where('name', 'like', '%' . $str . '%')
                       ->where('school_id',Auth::user()->school_id)->whereNotIn('id', $selected_student)->get();
                }

//                $user  = User::Where(function($query) use ($str)
//                {
//                    $query->where('role','student');
//                })
//                    ->Where(function($query) use ($str)
//                    {
//                        $query->orWhere('name', 'like', '%'.$str.'%')
//                            ->orWhere('parents_name', 'like', '%'.$str.'%');
//                    })
//                    ->get();

                $total_records = count($user);
                if($total_records > 0)
                {
                    if($total_records>6)
                    {
                        echo "<div style='overflow-y:scroll; overflow-x:hidden;  height:192px;'>";
                    }
                    else
                    {
                        echo "<div style='overflow-y:scroll; overflow-x:hidden;'>";
                    }
                    echo "<table  border='0' cellspacing='0' cellpadding='0' class='finders' width='250px' style='border:solid 1px #ccc; background:#f8f8f8;'>";

                    echo "<tr>";
                    echo "<td width='250px' valign='top' align='left'>";

                    // While there are results loop through them - fetching an Object (i like PHP5 btw!).
                    echo "<span style='color:#1D2C3C; font-size:14px;'>".'<li>&nbsp;&nbsp;<b>Student List</b></li></span>';
                    if($total_records > 0)
                    {
                        foreach($user as $key => $value)
                        {
                           // echo '<li onclick="fill('.$value->id.',"'.str_replace("\\","",$value->name).'")">'.str_replace("\\","",$value->name).'</li>';
                            echo '<li id="'.$value->id.'" onclick="fill('.$value->id.',\''.trim($value->name,' ').'\')">'.str_replace("\\","",$value->name).'</li>';

                            //  echo '<li onclick="fill('.$value['id'].')">'.$value['name'].'</li>';
                        }
                    }
                    else
                    {
                        echo "<br>&nbsp;&nbsp;<b>Your search term did not return any results.</b><br><br><span style='color:#B93969; font-weight:bold;'>&nbsp;Suggested list</span>";
                    }
                    echo "</tr></table>";

                }
                else
                {
                    echo  "<table><tr><td width='250px' align='center' style='padding:5px 0px 5px 0px;color:#444444;font-size: 13px;'>";
                    echo 'No student matched your search.';
                    echo "</td></tr></table><div>";
                }
            }

            else
            {
                // Dont do anything.
            } // There is a queryString.
    }
    public function import_excel()
    {
        $data=[];
        $data['menu']="Parents";
        return view('parents.import', $data);
    }

    public function import_data(Request $request)
    {

        $this->validate($request, [
            'parents_excel' => 'required',
        ]);

        try {

            $sheet1 = Excel::selectSheetsByIndex(0)->load($request->file('parents_excel'))->get();

            foreach($sheet1 as $row) {

                if (trim($row->parent_name) != '' && trim($row->school) != '' && trim($row->parent_email) != '' && trim($row->parent_password) != '' && trim($row->parent_mobile_no) != '') {

                    $school = School::where('name',trim($row->school))->first();

                    if (!empty($school)) {

                        $school_id = $school->id;

                        if(Auth::user()->role == "staff" && $school_id != Auth::user()->school_id) {
                            continue;
                        }

                        $dup_parent = Parents::where('email',trim($row->parent_email))->where('school_id', $school_id)->where('role', 'parent')->first();

                        if(empty($dup_parent)) {

                            $parents = new Parents();

                            $parents->school_id = $school_id;
                            $parents->name = $row->parent_name;
                            $parents->email = $row->parent_email;
                            $parents->password = bcrypt($row->parent_password);
                            $parents->mobile = $row->parent_mobile_no;
                            $parents->status = 'active';
                            $parents->role = 'parent';

                            $parents->save();

                        }
                    }
                }
            }

            \Session::flash('success', 'File Imported successfully.');
            return redirect('parents');
        } catch (\Exception $e) {
            \Session::flash('danger', $e->getMessage());
            return redirect('parents/import');
        }
    }

    public function export()
    {
        if(Auth::user()->role == "staff") {
            $data = Parents::select('name as Name','email as Email','school_id','mobile as Mobile')->where('role','parent')->where('school_id', Auth::user()->school_id)->get()->toArray();

        } else {
            $data = Parents::select('name as Name','email as Email','school_id','mobile as Mobile')->where('role','parent')->get()->toArray();
        }

        foreach($data as $key => $value) {
            $school_name = School::select('name')->where('id', $value['school_id'])->first();
            $data[$key]['School'] = $school_name['name'];
            unset($data[$key]['school_id']);
        }

        return Excel::create('Parents', function($excel) use ($data) {

            $excel->sheet('parents', function($sheet) use ($data) {
                $sheet->fromArray($data);
            });

        })->download('xlsx');

    }
}
