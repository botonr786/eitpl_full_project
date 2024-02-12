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
use App\Models\Leave\Leave_allocation;
use App\Models\Rate_master;
use App\Models\Masters\Company;
use Illuminate\Support\Facades\Crypt;
use Barryvdh\DomPDF\Facade\Pdf;
use Validator;
use DB;
Use \Carbon\Carbon;

class PayrollController extends Controller
{

    public function payrollList(Request $request)
    {
        try {
            $user = Auth::guard("api")->user();
            $emp_code = $request->emp_id;
            $org_id = $request->emid;

            $result = Payroll_detail::where('employee_id', $emp_code)
                ->orderBy('id', 'desc')
                ->limit(3)
                ->get();

            if (sizeof($result)) {
                // Generate URLs for each payroll record
                foreach ($result as $payroll) {
                    $payroll->url = url('payroll/payslip-details') . '/' . encrypt($payroll->employee_id) . '/' . encrypt($payroll->id);
                }

                return response([
                    'flag' => 1,
                    'status' => 200,
                    'message' => 'Payroll List',
                    'response' => [
                        'data' => $result,
                    ],
                ]);
            } else {
                return response([
                    'flag' => 0,
                    'status' => 400,
                    'message' => 'Not Found',
                    'response' => [
                        'data' => $result,
                    ],
                ]);
            }
        } catch (Exception $e) {
            return Helper::rj("Server Error.", 500);
        }
    }
    public function viewPayrollDetails($emp_id, $pay_dtl_id)
    {
        //dd($emp_id);
        // $email = Session::get('emp_email');
        // if (!empty($email)) {

            $emp_id = Crypt::decrypt($emp_id);
            $pay_dtl_id = Crypt::decrypt($pay_dtl_id);
            if ($emp_id) {
                    $data['payroll_rs'] = Payroll_detail::join('employee', 'payroll_details.employee_id', '=', 'employee.emp_code')
                        ->join('bank_masters', 'employee.emp_bank_name', '=', 'bank_masters.id')
                        ->join('monthly_employee_allowances', 'employee.emp_code', '=', 'monthly_employee_allowances.emp_code')
                        ->leftJoin('group_name_details', 'employee.emp_group', '=', 'group_name_details.id')
                        ->where('payroll_details.employee_id', '=', $emp_id)
                        ->where('payroll_details.id', '=', $pay_dtl_id)
                        ->select(
                            'payroll_details.*',
                            'employee.*',
                            'bank_masters.master_bank_name',
                            'group_name_details.group_name',
                            'monthly_employee_allowances.no_days_tiffalw'
                        )
                        ->get();

                $data['leave_hand'] = Leave_allocation::join('leave_types', 'leave_allocation.leave_type_id', '=', 'leave_types.id')
                    ->where('leave_allocation.employee_code', '=', $emp_id)
                    ->where('leave_allocation.leave_allocation_status', '=', 'active')
                    ->select('leave_allocation.*', 'leave_types.leave_type_name')
                    ->get();

                $montharr = explode('/', $data['payroll_rs'][0]->month_yr);
                $calculate_month = $montharr[0] - 2;

                if (strlen($calculate_month) == 1) {
                    $leave_calculate = "0" . $calculate_month;
                } else {
                    $leave_calculate = $calculate_month;
                }

                $caculate_month_for_leave = $leave_calculate . "/" . $montharr[1];

                $data['current_month_days'] = date('t', strtotime($montharr[1] . '-' . $montharr[0] . '-01'));

                $data['actual_payroll'] = Emp_actual_paystructure::where('emp_code', '=', $emp_id)
                    ->first();

                $data['company_rs'] = Company::orderBy('id', 'desc')->first();
                $data['rate_master'] = Rate_master::get();

                //dd($data);

                return view('payroll/vwpayslip', $data);
            }
        // } else {
        //     return redirect('/');
        // }
    }


}
