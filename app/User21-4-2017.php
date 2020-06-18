<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use SoftDeletes;
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    const LOCATION_HK = 'hk';
    const LOCATION_TAIWAN = 'tw';
    const LOCATION_CHINA = 'cn';
    const LOCATION_KO = 'ko';
    const LOCATION_SINGAPORE = 'sg';


    public static $location = [
        self::LOCATION_HK => 'Hong Kong',
        self::LOCATION_TAIWAN => 'Taiwan',
        self::LOCATION_CHINA => 'China',
        self::LOCATION_KO => 'Korean',
        self::LOCATION_SINGAPORE => 'Singapore',

    ];

    public static $status = [
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_INACTIVE => 'In Active',
    ];

    protected $fillable = [
        'name','email','password','role','status','location','language',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
}
