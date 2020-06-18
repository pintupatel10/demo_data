<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Device extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name','school_id','serial_no','location','description','device_type','status',
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    public static $status = [
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_INACTIVE => 'In Active',
    ];

    const Please = '';
    const Type_Main_Gate = 'main gate';
    const Type_Inner_Gate = 'inner gate';
   // const Type_Gate = 'gate';
    const Type_Library = 'library';
    const Type_Canteen = 'canteen';
    const Type_Class = 'class_room';
    const Type_Staff = 'staff_room';
    const Type_Wash = 'wash_room';
    const Type_Vehicle='vehicle';


    public static $device_type = [
        self::Please => 'Please select',
        self::Type_Main_Gate => 'Main Gate',
        self::Type_Inner_Gate => 'Inner Gate',

       // self::Type_Gate => 'Gate',
        self::Type_Library => 'Library',
        self::Type_Canteen => 'Canteen',
        self::Type_Class => 'Class Room',
        self::Type_Staff => 'Staff Room',
        self::Type_Wash => 'Wash Room',
        self::Type_Vehicle => 'Vehicle',
    ];

}
