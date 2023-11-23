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

     public static function resp($message = '', $flag = 1, $data = [], $token = [])
    {
        $status = 200;
        return [
            'status'  => $status,
            'flag'    => $flag,
            'message' => $message,
            'data'    => $data,
            'token'   => $token,
        ];
    }

    public static function rj($message = '', $flag = 1, $data = [], $token = [])
    {
        $response = self::resp($message, $flag, $data, $token);
        return response()->json($response, $response['status']);
    }
}
