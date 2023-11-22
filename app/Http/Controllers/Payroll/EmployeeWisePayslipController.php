<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Employee\Emp_actual_paystructure;
use App\Models\Masters\Company;
use App\Models\Rate_master;
use Illuminate\Http\Request;
use App\Models\Payroll\Payroll_detail;
use App\Models\Employee;
use App\Models\Leave\Leave_allocation;
use Illuminate\Support\Facades\Crypt;
use Barryvdh\DomPDF\Facade\Pdf;
use Session;
use Validator;
use View;
use DB;

class EmployeeWisePayslipController extends Controller
{
    public function getEmployeeWisePayslip()
    {
        $email = Session::get('emp_email');
        if (!empty($email)) {
            $data['result'] = '';
            $data['monthlist'] = Payroll_detail::select('month_yr')->distinct('month_yr')->get();
            $data['employeeslist'] = Employee::get();
            $Payroll_details_rs = '';
            return view('payroll.employeewise-view-payslip', $data);
            dd($data);
        } else {
            return redirect('/');
        }
    }
    public function showEmployeeWisePayslip(Request $request)
    {
        $email = Session::get('emp_email');
        if (!empty($email)) {
            $employeeslist = Employee::get();
            $Payroll_details_rs = $result = '';
            $emp_id = $request->emp_code; //dd($emp_id);
            $month_yr = $request->month_yr;
            $validator = Validator::make($request->all(), [
                'month_yr' => 'required',
                //        'emp_code' => ['required',
                //        Rule::exists('Payroll_details')->where(function ($query) use($emp_id) {
                //            $query->where('emp_code', $emp_id);
                //        }),
                //        ],
                [
                    'month_yr.required' => 'Month Year Required',
                ],
            ]);

            if ($validator->fails()) {
                return redirect('payroll/vw-employeewise-view-payslip')->withErrors($validator)->withInput();
            }

            if ($emp_id == '') {

                //        $company_rs=Company::where('company_status','=','active')->select('id','company_name')->get();
                //        $Payroll_details_rs=PayrollDetails::where('emp_code','=',$emp_id)->where('company_id','=',$company_id)->select('*')->get()->first();

                $Payroll_details_rs = Payroll_detail::leftJoin('employees', 'payroll_details.employee_id', '=', 'employees.emp_code')
                    ->where('payroll_details.month_yr', '=', $month_yr)
                    ->select('payroll_details.*', 'employees.old_emp_code')
                    ->get();
            } else {

                $Payroll_details_rs = Payroll_detail::leftJoin('employee', 'payroll_details.employee_id', '=', 'employee.emp_code')
                    ->where('payroll_details.month_yr', '=', $month_yr)
                    ->where('payroll_details.employee_id', '=', $emp_id)
                    ->select('payroll_details.*', 'employee.old_emp_code')
                    ->get();

            }
            if (count($Payroll_details_rs) != 0) {
                foreach ($Payroll_details_rs as $payroll) {

                    $result .= '<tr style="text-align:center;">
							<td>' . $payroll->old_emp_code . '</td>
							<td>' . $payroll->emp_name . '</td>
							<td>' . $payroll->emp_designation . '</td>
							<td>' . $payroll->month_yr . '</td>
							<td>' . $payroll->emp_gross_salary . '</td>
							<td>' . $payroll->emp_total_deduction . '</td>
							<td>' . $payroll->emp_net_salary . '</td>
							<td><a href="' . url('payroll/payslip') . '/' . encrypt($payroll->employee_id) . '/' . encrypt($payroll->id) . '" target="_blank"><i class="fa fa-eye"></i></a></td>
						</tr>';
                    //dd($result);
                }
            } else {
                Session::flash('error', 'Payslip is not Generated .');
            }

            //dd($result);
            $month_yr_new = $month_yr;
            $emp_id_new = $emp_id;
            $monthlist = Payroll_detail::select('month_yr')->distinct('month_yr')->get();

            return view('payroll/employeewise-view-payslip', compact('result', 'Payroll_details_rs', 'month_yr_new', 'emp_id_new', 'monthlist', 'employeeslist'));
        } else {
            return redirect('/');
        }
    }
    public function viewPayrollDetails($emp_id, $pay_dtl_id)
    {
        $email = Session::get('emp_email');
        if (!empty($email)) {

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
        } else {
            return redirect('/');
        }
    }

}
