<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class School extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name','medium','type','phone','mobile','website','image','principal_name','trustee_name',
        'detail','tot_strength','start_time','end_time','week_start_time','week_end_time','refer_by',
        'market_by','Latitude','Longitude','Address','status',
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    public static $status = [
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_INACTIVE => 'In Active',
    ];

    const Please = '';
    const English = 'English';
    const Gujarati = 'Gujarati';
    const Hindi = 'Hindi';
    const Sanskrit = 'Sanskrit';
    const EnglishGujarati = 'English & Gujarati';
    const EnglishHindi = 'English & Hindi';
    public static $medium = [
        self::Please => 'Please select',
        self::English => 'English',
        self::Gujarati => 'Gujarati',
        self::Hindi => 'Hindi',
        self::Sanskrit => 'Sanskrit',
        self::EnglishGujarati => 'English & Gujarati',
        self::EnglishHindi => 'English & Hindi',
    ];

    const Type_State = 'State';
    const Type_CBSC = 'CBSC';
    const Type_IGCE = 'IGCE';
    const Type_ST_CB_IGCE = 'State-CBSE-IGCE';
    const Type_ST_CB = 'State-CBSE';
    const Type_CB_IGCE = 'CBSE-IGCE';
    const Type_ST_IGCE = 'State-IGCE';

    public static $type = [
        self::Please => 'Please select',
        self::Type_State => 'State',
        self::Type_CBSC => 'CBSC',
        self::Type_IGCE => 'IGCE',
        self:: Type_ST_CB_IGCE => 'State - CBSE - IGCE',
        self:: Type_ST_CB => 'State - CBSE',
        self:: Type_CB_IGCE => 'CBSE - IGCE',
        self:: Type_ST_IGCE => 'State - IGCE',
    ];

    public function school_staff()
    {
        return $this->hasMany('App\Staff','school_id');
    }

    public function school_division()
    {
        return $this->hasMany('App\Division','school_id');
    }

    public function school_class()
    {
        return $this->hasMany('App\Class_Master','school_id');
    }

    public function school_calendar()
    {
        return $this->hasMany('App\Calendar','school_id');
    }
    public function Attendance(){
        return $this->hasMany('App\Attendance','school_id');
    }
}
