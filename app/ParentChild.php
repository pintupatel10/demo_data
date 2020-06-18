<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParentChild extends Model
{
    protected $table="parent_child";
    use SoftDeletes;

    protected $fillable = ['parent_id','student_id','status'];

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    public static $status = [
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_INACTIVE => 'In Active',
    ];

    public function parents_child()
    {
        return $this->belongsTo('App\Parents', 'parent_id');
    }

    public function parents_student()
    {
        return $this->belongsTo('App\Student', 'student_id');
    }
}
