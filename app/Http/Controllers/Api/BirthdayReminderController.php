<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use DB;
use Carbon\Carbon;

class BirthdayReminderController extends Controller
{
    
    public function getBirthdayList(Request $request){
        
        try{
            

            $user = Auth::guard("api")->user();
            $currentDate = now();
            $emp_code=$request->emp_id;
            $org_id=$request->emid;
            $result = Employee::select(
                'emp_fname', 
                'emp_mname', 
                'emp_lname', 
                'em_phone', 
                'profileimage'
            )
            ->selectRaw("IFNULL(emp_fname, '') as emp_fname")
            ->selectRaw("IFNULL(emp_mname, '') as emp_mname")
            ->selectRaw("IFNULL(emp_lname, '') as emp_lname")
            ->selectRaw("IFNULL(em_phone, '') as em_phone")
            ->selectRaw("IFNULL(profileimage, '') as profileimage")
            ->where('status', '=', 'active')
            /*->where(function ($query) {
                $query->whereRaw('DAY(dateofbirth) = DAY(NOW())')
                      ->whereRaw('MONTH(dateofbirth) = MONTH(NOW())');
            })*/
            ->whereRaw("DAYOFMONTH(dateofbirth) = ? AND MONTH(dateofbirth) = ?", [$currentDate->day, $currentDate->month])
            ->where('emid', '=', $org_id)
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
