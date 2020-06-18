<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'id','sender_id','receiver_id','message','type',
    ];

    const NOTIFICATION_ALL = 'All';
    const NOTIFICATION_PRINCIPAL = 'Principal';
    const NOTIFICATION_PARENT = 'Parents';
    const NOTIFICATION_TEACHER = 'Teacher';
   // const NOTIFICATION_STUDENT = 'Student';

    public static $notification_admin = [
        self:: NOTIFICATION_ALL => 'All',
        self:: NOTIFICATION_PRINCIPAL => 'Principal',
        self:: NOTIFICATION_PARENT => 'Parents',
        self:: NOTIFICATION_TEACHER => 'Teacher',
       // self:: NOTIFICATION_STUDENT => 'Student',
    ];

    public static $notification_teacher = [
        self:: NOTIFICATION_PARENT => 'Parents',
    ];

    public static $notification_principal = [
        self:: NOTIFICATION_PARENT => 'Parents',
        self:: NOTIFICATION_TEACHER => 'Teacher',

    ];


    const Transactional_pre = 'present';
    const Transactional_ab = 'absent';
    const Transactional_emc = 'emergency_close';
    const Transactional_inj = 'injured';
    const Transactional_holi = 'holiday';
    const Transactional_vc = 'vacation';

    public static $Transactional_Type  = [
        self:: Transactional_pre => 'Present',
        self:: Transactional_ab => 'Absent',
        //self:: Transactional_emc => 'Injured',
        self:: Transactional_inj => 'Emergency Close',
        self:: Transactional_holi => 'Holiday',
        self:: Transactional_vc => 'Vacation',
    ];
}
