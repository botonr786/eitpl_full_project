<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Payroll\Payroll_detail;
use App\Models\Employee\Emp_actual_paystructure;
use App\Models\Employee;
use App\Models\Masters\Company;
use Illuminate\Support\Facades\Crypt;
use Barryvdh\DomPDF\Facade\Pdf;
use Validator;
use DB;
Use \Carbon\Carbon;

class PayrollController extends Controller
{

    public function payrollList(Request $request){
        try{
            $user = Auth::guard("api")->user();
            $emp_code=$request->emp_id;
            $org_id=$request->emid;
            $result = Payroll_detail::where('employee_id', $emp_code)
            ->orderBy('id', 'desc')
            ->limit(3)
            ->get();
            if(sizeof($result)){
            return response(array('flag'=>1, 'status'=>200,'message'=>'Payroll List','response' => [
                'data' => $result,
            ],));
            }else{
            return response(array('flag'=>0, 'status'=>400,'message'=>'Not Found','response' => [
                'data' => $result,
            ],));
            }

         }catch(Exception $e){
            return Helper::rj("Server Error.", 500);
        }
    }

}
