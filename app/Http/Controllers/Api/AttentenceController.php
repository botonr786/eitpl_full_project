<?php
namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\Attendance\Attendance;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use DB;

class AttentenceController extends Controller
{
    public function __construct()
    {
        $this->_model = new Attendance();
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
        $dynamicFlag = 1;
        try {
            $user = Auth::guard("api")->user();
                $emp_code = $request->user_id;
                $date = date("Y-m-d");
                $month = date("m/Y", strtotime("now"));

                //employee check validation
                $emp_check = $this->_model->checkEmployee($emp_code);

                if ($emp_check == null) {
                    return Helper::rj("Employee Not Found", 1);
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
                                "user_id" => "required",
                                "time_in" => "required",
                                "time_in_location" => "required",
                                "latitudes" => "required",
                                "longitude" => "required",
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
                                "longitudes" => $request->longitude,
                                "loginStatus" => true,
                                "logoutStatus" => false,
                            ];

                            $response = Attendance::insert(
                                $attentenceArray
                            );
                            if ($response == true) {
                                $attendence = Attendance::where("date", $date)
                                ->where("employee_code", $emp_code)
                                ->first();
                                return Helper::rj("Attendence login success", 1,$attendence);
                            } else {
                                return Helper::rj("somthing wrong", 0);
                            }
                        } else {
                            return Helper::rj("Already Login please logout", 0);
                        }
                    }
                    if ($request->type === "logout") {
                        $attendenceId = Attendance::where("date", $date)
                            ->where("employee_code", $emp_code)
                            ->first();

                        $validator = Validator::make($request->all(), [
                            "user_id" => "required",
                            "time_out" => "required",
                            "time_out_location" => "required",
                        ]);

                        if ($validator->fails()) {
                            return $this->sendError(
                                "Validation Error.",
                                $validator->errors()
                            );
                        }

                        $response = Attendance::where("id", $attendenceId->id)->update([
                            "time_out" => $request->time_out,
                            "time_out_location" => $request->time_out_location,
                            "duty_hours" => $request->duty_hours,
                            "logoutStatus" => true,
                        ]);
                        //$response = Attendance::where("id", $attendenceId->id)->update($attentenceArray);

                        if ($response == true) {
                            $attendence = Attendance::where("date", $date)
                            ->where("employee_code", $emp_code)
                            ->first();
                            return Helper::rj("Attendence logout success", 1,$attendence);
                        } else {
                            return Helper::rj("something wrong", 0);
                        }
                    }
                    } else {
                        return Helper::rj("holiday exits", 0);
                    }
        } catch (Exception $e) {
            return Helper::rj("Server Error.", 500);
        }
    }
}
