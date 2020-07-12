<?php

namespace App\Helpers;

use Auth;

class SiteHelper
{

	public static function isDoctor()
	{
		return (Auth::user()->role_id == 2) ?  true  : false;
	}

	public static function isPatient()
	{
		return (Auth::user()->role_id == 1) ?  true  : false;
	}

	public static function implode($array,$key = null,$glue = ",")
	{
		if($key !== null) {
			$m = [];
			foreach($array as $a) {
				$m[] = $a[$key];
			}

			return implode($glue,$m);
		}else{
			return implode($glue, $array);
		}
	}

}
