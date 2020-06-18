<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Parents extends Model
{
    protected $table="users";
    use SoftDeletes;

    protected $fillable = ['email','password','school_id','name','mobile','status','role'];

    const USER_ADMIN = 'admin';
    const USER_STAFF = 'staff';
    const USER_STUDENT = 'student';
    const USER_PARENT = 'parent';

    public static $user_roles = [
        self::USER_ADMIN => 'Admin',
        self::USER_STAFF => 'Staff',
        self::USER_STUDENT => 'Student',
        self::USER_PARENT => 'Parent',
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    public static $status = [
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_INACTIVE => 'In Active',
    ];

    public function parents_school()
    {
        return $this->belongsTo('App\School', 'school_id');
    }

    public function parent_child()
    {
        return $this->hasMany('App\ParentChild','parent_id');
    }

    public static function boot()
    {
        static::deleted(function($model) {

            foreach($model->parent_child as $det)
                $det->delete();

        });
        parent::boot();
    }

    public function scopeWithSchoolName($query) {
        $query->leftjoin('schools','schools.id', '=', 'users.school_id')->addSelect('schools.name as school_name');
    }
}
