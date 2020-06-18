<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    protected $table="attendance";
    use SoftDeletes;

    protected $fillable = [
        'school_id','student_id','student_name','class_name','class_division','school_in_time','on_leave','latittude','longitude',
        'school_out_time','attendance_date','attendance_time','device_id','staff_id','staff_name','staff_role','status','rfid_no',
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    public static $status = [
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_INACTIVE => 'In Active',
    ];

    public function school()
    {
        return $this->belongsTo('App\School', 'school_id');
    }

    public function Class_Master()
    {
        return $this->belongsTo('App\Class_Master', 'class_name');
    }
    
    public function Student()
    {
        return $this->belongsTo('App\Student', 'student_id');
    }

    public function AttendanceDetail()
    {
        return $this->hasMany('App\AttendanceDetail','attendance_id');
    }
}
