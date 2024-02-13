<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\Attendance\Attendance;
use App\Models\LeaveManagement\Leave_type;
use App\Models\LeaveApprover\Leave_apply;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \Illuminate\Auth\AuthenticationException;
use Validator;
use DB;
Use \Carbon\Carbon;

class LeaveController extends Controller
{

    public function leaveType(Request $request){
        try{
            $user = Auth::guard("api")->user();
            $emp_code=$request->emp_id;
            $org_id=$request->emid;
            $result = Leave_type::where('emid', $org_id)
            ->where('leave_type_status','active')
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

         }catch(AuthenticationException $e){
            return Helper::rj("Server Error.", 500);
        }
    }
    public function leaveList(Request $request){
        try{
            $user = Auth::guard("api")->user();
            $emp_code=$request->emp_id;
            $org_id=$request->emid;
            $type=$request->leave_type;
            $result = Leave_apply::join('leave_type', 'leave_apply.leave_type', '=', 'leave_type.id')
            ->where('leave_apply.employee_id', $emp_code)
            ->where('leave_apply.status', $type)
            ->select('leave_apply.*', 'leave_type.leave_type_name')
            ->orderBy('leave_apply.id','desc')
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

         }catch(AuthenticationException $e){
            return Helper::rj("Server Error.", 500);
        }
    }
    public function leaveApply(Request $request){
        try{
            $user = Auth::guard("api")->user();
            $emp_code=$request->emp_id;
            $employee_name=$request->employee_name;
            $org_id=$request->emid;
            $type=$request->leave_type;
            $from_date=$request->date_from;
            $to_date=$request->date_to;
            $emp_reporting_auth=$request->reportingauthority;
            $emp_lv_sanc_auth=$request->leaveauthority;
            $half_cl=$request->half_cl;
            $status_remarks=$request->status_remarks;

            $from_date_cal = Carbon::parse($request->date_from);
            $to_date_cal = Carbon::parse($request->date_to);
            $diffInDays = $to_date_cal->diffInDays($from_date_cal);
            $no_of_leave = $diffInDays+1;
            $date = date('Y-m-d');
            $status = 'NOT APPROVED';

            $leaveApply = new Leave_apply();

            // Set the model attributes
            $leaveApply->employee_id = $emp_code;
            $leaveApply->employee_name = $employee_name;
            $leaveApply->emp_reporting_auth = $emp_reporting_auth;
            $leaveApply->emp_lv_sanc_auth = $emp_lv_sanc_auth;
            $leaveApply->date_of_apply = $date;
            $leaveApply->leave_type = $type;
            $leaveApply->half_cl = $half_cl;
            $leaveApply->from_date = $from_date;
            $leaveApply->to_date = $to_date;
            $leaveApply->status_remarks = $status_remarks;
            $leaveApply->no_of_leave = $no_of_leave;
            $leaveApply->status =  $status;
            $leaveApply->emid = $org_id;
            $saveSuccess = $leaveApply->save();
            if ($saveSuccess) {
                return response([
                    'flag' => 1,
                    'status' => 200,
                    'message' => 'Leave application saved successfully.',
                    'response' => [
                        'leaveApply' => $leaveApply,
                    ],
                ]);
            } else {
                return response([
                    'flag' => 0,
                    'status' => 400,
                    'message' => 'Failed to save leave application.',
                ]);
            }

         }catch(AuthenticationException $e){
            return Helper::rj("Server Error.", 500);
        }
    }

}
