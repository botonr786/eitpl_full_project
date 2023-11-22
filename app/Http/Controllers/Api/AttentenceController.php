<?php
namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\Attendance\Process_attendance;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use DB;

class AttentenceController extends Controller
{
    public function __construct()
    {
        $this->_model = new Process_attendance();
    }
    //validation response function
    public function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            "success" => false,
            "message" => $error,
        ];

        if (!empty($errorMessages)) {
            $response["data"] = $errorMessages;
        }

        return response()->json($response, $code);
    }

    //end
    public function attentenceAdd(Request $request)
    {
        try {
            $user = Auth::guard("api")->user();
            $emp_code = $user->employee_id;
            $date = date("Y-m-d");
            $month = date("y-m");

            //employee check validation
            $emp_check = $this->_model->checkEmployee($emp_code);

            if ($emp_check == null) {
                return Helper::rj("Employee Not Found", 401);
            }
            $emid = $emp_check->emid;
            $emp_code = $emp_check->emp_code;
            $emp_Name = $emp_check->emp_fname . " " . $emp_check->emp_lname;

            //holiday validation
            $holidayValidate = $this->_model->holidayValidation($date);
            if (!sizeof($holidayValidate)) {
                $checkAtten = $this->_model->dayWiseCheckAttentence(
                    $emp_code,
                    $date
                );
                // attentence login
                if ($request->type === "login") {
                    if (!sizeof($checkAtten)) {
                        $validator = Validator::make($request->all(), [
                            "time_in" => "required",
                            "time_in_location" => "required",
                            "latitudes" => "required",
                        ]);

                        if ($validator->fails()) {
                            return $this->sendError(
                                "Validation Error.",
                                $validator->errors()
                            );
                        }

                        $attentenceArray = [
                            "employee_code" => $emp_code,
                            "employee_name" => $emp_Name,
                            "date" => $date,
                            "time_in" => $request->time_in,
                            "month" => $month,
                            "time_in_location" => $request->time_in_location,
                            "emid" => $emid,
                            "latitudes" => $request->latitudes,
                            "longitudes" => $request->longitudes,
                            "loginStatus" => true,
                            "logoutStatus" => false,
                        ];

                        $response = Process_attendance::insert(
                            $attentenceArray
                        );
                        if ($response == true) {
                            return Helper::rj("Attendence login success", 200);
                        } else {
                            return Helper::rj("somthing else", 401);
                        }
                    } else {
                        return Helper::rj("Day wise alredy exits", 401);
                    }
                }
                if ($request->type === "logout") {
                    $attendenceId = Process_attendance::where("date", $date)
                        ->where("employee_code", $emp_code)
                        ->first();

                    $validator = Validator::make($request->all(), [
                        "time_out" => "required",
                        "time_out_location" => "required",
                    ]);

                    if ($validator->fails()) {
                        return $this->sendError(
                            "Validation Error.",
                            $validator->errors()
                        );
                    }

                    $attentenceArray = [
                        "time_out" => $request->time_out,
                        "time_out_location" => $request->time_out_location,
                        "duty_hours" => $request->duty_hours,
                        "loginStatus" => false,
                        "logoutStatus" => true,
                    ];
                    $response = DB::table("attandence")
                        ->where("id", $attendenceId->id)
                        ->update($attentenceArray);

                    if ($response == true) {
                        return Helper::rj("Attendence logout success", 200);
                    } else {
                        return Helper::rj("somthing else", 401);
                    }
                }
            } else {
                return Helper::rj("holiday exits", 401);
            }
        } catch (Exception $e) {
            return Helper::rj("Server Error.", 500);
        }
    }
}
