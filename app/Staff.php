<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    protected $table="users";
    use SoftDeletes;

    protected $fillable = ['email','password','school_id','name','address','birthdate','blood_group','mobile','school_time','notes','rfid_no','staff_role','status','role'];

    const USER_ADMIN = 'admin';
    const USER_STAFF = 'staff';
    const USER_STUDENT = 'student';

    public static $user_roles = [
        self::USER_ADMIN => 'Admin',
        self::USER_STAFF => 'Staff',
        self::USER_STUDENT => 'Student',
    ];

    const STAFF_PRINCIPAL = 'principal';
    const STAFF_TEACHER = 'teacher';
    const STAFF_ACCOUNTANT = 'accountant';
    const STAFF_PEON = 'peon';

    public static $staff_roles = [

        self::STAFF_PRINCIPAL => 'Principal',
        self::STAFF_TEACHER => 'Teacher',
        self::STAFF_ACCOUNTANT => 'Accountant',
        self::STAFF_PEON => 'Peon',

    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    public static $status = [
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_INACTIVE => 'In Active',
    ];

    public function staff_school()
    {
        return $this->belongsTo('App\School', 'school_id');
    }

}
