<?php
namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\UserModel;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use DB;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->_model = new UserModel();
    }

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

    public function doLogin(Request $request)
    {
        $dynamicFlag = 1;

        try {
            $validator = Validator::make($request->all(), [
                "email" => "required|email",
                "password" => "required",
            ]);

            if ($validator->fails()) {
                return $this->sendError(
                    "Validation Error.",
                    $validator->errors()
                );
            }
            $checkuser = $this->_model->userfind(
                $request->email,
                $request->password
            );

            if ($checkuser == null) {
                $dynamicFlag = 0;
                return Helper::rj("Not a valid credential.",$dynamicFlag);
            }
            $data = UserModel::where("email", $request->email)->first();
            $token = $data->createToken("token")->accessToken;
            if ($checkuser->user_type === "employer") {
                $user_id = $checkuser->employee_id;
                $user = UserModel::where("employee_id", $user_id)->first();
                $deviceToken = $request->device_token;

                if ($user) {
                     $user->update(['device_token' => $deviceToken]);
                     $checkuser = UserModel::where("employee_id", $user_id)->first();
                    return Helper::rj(
                        "Employeer login success",
                        $dynamicFlag,
                        $checkuser,
                        $token
                    );
                }else{
                    $dynamicFlag=0;
                    return Helper::rj(
                        "User not found",
                        $dynamicFlag,
                        $checkuser,
                        $token
                    );
                }
            } else {
                $user_id = $checkuser->employee_id;
                $user = UserModel::where("employee_id", $user_id)->first();
                $deviceToken = $request->device_token;

                if ($user) {
                     $user->update(['device_token' => $deviceToken]);
                     $checkuser = UserModel::where("employee_id", $user_id)->first();
                    return Helper::rj(
                        "Employee login success",
                        $dynamicFlag,
                        $checkuser,
                        $token
                    );
                }else{
                    $dynamicFlag=0;
                    return Helper::rj(
                        "User not found",
                        $dynamicFlag,
                        $checkuser,
                        $token
                    );
                }
            }
        } catch (Exception $e) {
            return Helper::rj("Server Error.", 500);
        }
    }
}
?>
