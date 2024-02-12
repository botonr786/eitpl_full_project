<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Models\Holiday\HolidayList;
use Illuminate\Support\Facades\Auth;

class HolidayController extends Controller
{
    public function employeeHolidays(Request $request){
        try{
            $startDate=date('Y-01-01');
            $endDate=date('Y-12-31');

            $user = Auth::guard("api")->user();
            $emp_code=$request->emp_id;
            $org_id=$request->emid;
            $result = HolidayList::join('holiday_type', 'holiday.holiday_type', 'holiday_type.id')
              ->select('holiday.*', 'holiday_type.name')
              ->where('holiday.emid', $org_id)
              ->whereBetween('holiday.from_date', [$startDate, $endDate]) // Replace 'date_column' with the actual date column name
              ->get();
            if(sizeof($result)){
                return response(array('flag'=>1, 'status'=>200,'message'=>'Leave Type List','response' => [
                    'data' => $result,
                ],));
                }else{
                return response(array('flag'=>0, 'status'=>400,'message'=>'Not Found','response' => [
                    'data' => $result,
                ],));
                }

        }
        catch(Exception $e){
            return Helper::rj("Server Error.", 500);
        }

    }

}
