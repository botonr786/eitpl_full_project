<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Helper
{

	/**
	 * Show date with date format.
	 * @param date / datetime $date
	 * @param boolean $showTime [show time also]
	 * @param string $dateFormat custom date format [any custom date format, which is not default]
	 * @param string $timezone [User timezone]
	 * @version:    1.0.0.5
	 * @author:     Somnath Mukherjee
	 */
    
    public static function resp($message = '', $status = 200, $data = [],$token= [])
	{
		return [
			'status'  => $status,
			'message' => $message,
			'data'    => $data,
            'token' =>$token,
		];
	}

     public static function rj($message = '', $headerStatus = 200, $data = [],$token= [])
	{
		$data = self::resp($message, $headerStatus, $data,$token);
		return response()->json($data, $headerStatus);
	}
}