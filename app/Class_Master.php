<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Class_Master extends Model
{
    use SoftDeletes;
    protected $table="class";

    protected $fillable = [
        'school_id','name','status',
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

    public function Attendance(){
        return $this->hasMany('App\Attendance','class_name');
    }

    public function Division(){
        return $this->hasMany('App\Division','class_id');
    }

}
