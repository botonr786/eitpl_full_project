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
    public $successStatus = 200;

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
                return Helper::rj("Not a valid credential.", 401);
            }
            $data = UserModel::where("email", $request->email)->first();
            $token = $data->createToken("token")->accessToken;
            if ($checkuser->user_type === "employer") {
                return Helper::rj(
                    "Employer login Success",
                    $this->successStatus,
                    $checkuser,
                    $token
                );
            } else {
                return Helper::rj(
                    "Employe login Success",
                    $this->successStatus,
                    $checkuser,
                    $token
                );
            }
        } catch (Exception $e) {
            return Helper::rj("Server Error.", 500);
        }
    }
}
?>
