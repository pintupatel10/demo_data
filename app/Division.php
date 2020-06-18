<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Division extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'school_id','class_id','division','status',
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    public static $status = [
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_INACTIVE => 'In Active',
    ];

    const DIVISION_A = 'A';
    const DIVISION_B = 'B';
    const DIVISION_C = 'C';
    const DIVISION_D = 'D';
    const DIVISION_E = 'E';
    const DIVISION_F = 'F';

    public static $divi = [
        self::DIVISION_A => 'A',
        self::DIVISION_B => 'B',
        self::DIVISION_C => 'C',
        self::DIVISION_D => 'D',
        self::DIVISION_E => 'E',
        self::DIVISION_F => 'F',
    ];

    public function school()
    {
        return $this->belongsTo('App\School', 'school_id');
    }

    public function classm()
    {
        return $this->belongsTo('App\Class_Master', 'class_id');
    }

}
