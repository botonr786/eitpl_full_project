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

     public static function resp($message = '', $flag = 1, $data = [], $token = [],$todayLogin=[])
    {
        $status = 200;
        return [
            'status'  => $status,
            'flag'    => $flag,
            'message' => $message,
            'todayLogin' =>$todayLogin,
            'data'    => $data,
            'token'   => $token,
        ];
    }

    public static function rj($message = '', $flag = 1, $data = [], $token = [],$todayLogin=[])
    {
        $response = self::resp($message, $flag, $data, $token,$todayLogin);
        return response()->json($response, $response['status']);
    }

    public static function responseData($message = '', $flag = 1, $data = [],$todayLogin=[],$isHoliday=[],$todayLogout=[])
    {
        $status = 200;
        return [
            'status'  => $status,
            'flag'    => $flag,
            'message' => $message,
            'todayLogin' =>$todayLogin,
            'isHoliday' => $isHoliday,
            'todayLogout'=>$todayLogout,
            'data' =>$data,
        ];
    }

    public static function res($message = '', $flag = 1, $data = [],$todayLogin=[],$isHoliday=[],$todayLogout=[])
    {
        $response = self::responseData($message, $flag, $data,$todayLogin,$isHoliday,$todayLogout);
        return response()->json($response, $response['status']);
    }

    public static function attendence($message = '', $flag = 1, $halfDayData = [],$fullDayData = [],$totakWorkingDay=[],$data=[])
    {
        $status = 200;
        return [
            'status'  => $status,
            'flag'    => $flag,
            'message' => $message,
            'halfDay' => $halfDayData,
            'fullDay' => $fullDayData,
            'totalWorkingDay' => $totakWorkingDay,
            'data' =>$data
        ];
    }

    public static function resAttendence($message = '', $flag = 1, $halfDayData = [],$fullDayData = [],$totakWorkingDay=[],$data=[])
    {
        $response = self::attendence($message, $flag, $halfDayData,$fullDayData,$totakWorkingDay,$data);
        return response()->json($response, $response['status']);
    }
}
