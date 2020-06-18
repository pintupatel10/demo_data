<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    protected $table="users";
    use SoftDeletes;

    protected $fillable = ['roll_no','school_id','class_id','division','name','address','birthdate','blood_group','mobile','school_time','parents_name','notes','rfid_no','status','role'];

    const USER_ADMIN = 'admin';
    const USER_STAFF = 'staff';
    const USER_STUDENT = 'student';

    public static $user_roles = [
        self::USER_ADMIN => 'Admin',
        self::USER_STAFF => 'Staff',
        self::USER_STUDENT => 'Student',
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    public static $status = [
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_INACTIVE => 'In Active',
    ];

    public function student_school()
    {
        return $this->belongsTo('App\School', 'school_id');
    }

    public function student_class()
    {
        return $this->belongsTo('App\Class_Master', 'class_id');
    }
    
    public function Attendance(){
        return $this->hasMany('App\Attendance','student_id');
    }

//    public function AttendanceToday(){
//        return $this->hasMany('App\Attendance','student_id')->where('attendance_date',Carbon::today()->format('Y-m-d'));
//    }
}
