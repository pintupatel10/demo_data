<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceDetail extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'school_id','student_id','student_name','class_name','class_division','school_in_time','on_leave','latittude','longitude',
        'school_out_time','attendance_date','attendance_time','device_id','staff_id','staff_name','staff_role','status','rfid_no','attendance_id',
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    public static $status = [
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_INACTIVE => 'In Active',
    ];

    public function Attendance()
    {
        return $this->belongsTo('App\Attendance', 'attendance_id');
    }
}
