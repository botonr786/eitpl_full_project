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
Use \Carbon\Carbon;

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
            $day = now()->format('l');

                $emp_codes = $request->emp_id;
                $date = date("Y-m-d");
                $month = date("m/Y", strtotime("now"));
                //employee check validation
                $emp_check = $this->_model->checkEmployee($emp_codes);

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
                    $todayLogin=['todayLogin'=>false];
                    if($request->type ==null){
                    $nowdaycheck=Attendance::where('date',$date)
                     ->where("employee_code", $emp_code)
                    ->first();
                   
                    
                   
                    if($nowdaycheck){
                        
                        if (isset($nowdaycheck->logoutStatus)) {
                            if ($nowdaycheck->logoutStatus == 1) {
                                return Helper::res("Already logged in", 1, $nowdaycheck, true, false, true);
                            } else {
                                return Helper::res("Already logged in", 1, $nowdaycheck, true, false, false);
                            }
                        } else {
                            // Handle the case when $nowdaycheck->logoutStatus is not set
                            // You might want to return an error response or handle it accordingly
                            return Helper::res("logoutStatus is not set", 1, $nowdaycheck, false, true, false);
                        }
                        
                        // if($nowdaycheck->logoutStatus==1){
                        //     return Helper::res("Alredy login", 1,$nowdaycheck,true,false,true);
                        // }else{
                        //   return Helper::res("Alredy login", 1,$nowdaycheck,true,false,false); 
                        // }
                        
                        
                    }else{
                        return response(['flag'=>0,'status'=>400, 'message'=>'not Login','todayLogin'=>false, 'todayLogout'=>false,'isHoliday'=>false]);
                    }

                    }
                    if ($request->type === "login") {
                        if (!sizeof($checkAtten)) {
                            $validator = Validator::make($request->all(), [
                                "time_in" => "required",
                                "time_in_location" => "required",
                                "latitudes" => "required",
                                "longitudes" => "required",
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
                                'day' =>$day,
                                "time_in" => $request->time_in,
                                "month" => $month,
                                "time_in_location" => $request->time_in_location,
                                "emid" => $emid,
                                "latitudes" => $request->latitudes,
                                "longitudes" => $request->longitudes,
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
                                return response(['flag'=>1,'status'=>200,'message'=>'Attendence login success','todayLogin'=>true,'isHoliday'=>false,'todayLogout'=>false,'data'=>$attendence]);
                            } else {
                                return response(['flag'=>0, 'message'=>'somthing is wrong','todayLogin'=>false,'isHoliday'=>false]);
                            }
                        } else {
                            return response(['flag'=>0,'status'=>200,'message'=>'today alredy Login','todayLogin'=>true,'todayLogout'=>true,'isHoliday'=>false]);
                        }
                    }
                    if ($request->type === "logout") {
                        $attendenceId = Attendance::where("date", $date)
                            ->where("employee_code", $emp_code)
                            ->first();

                        $validator = Validator::make($request->all(), [
                            "time_in" => "required",
                            "time_in_location" => "required",
                        ]);

                        if ($validator->fails()) {
                            return $this->sendError(
                                "Validation Error.",
                                $validator->errors()
                            );
                        }

                        $response = Attendance::where("id", $attendenceId->id)->update([
                            "time_out" => $request->time_in,
                            "time_out_location" => $request->time_in_location,
                            "duty_hours" => $request->duty_hours,
                             'logout_latitude'=>$request->latitudes,
                             'logout_longitude'=>$request->longitudes,
                            "logoutStatus" => true,
                        ]);
                        //$response = Attendance::where("id", $attendenceId->id)->update($attentenceArray);

                        if ($response == true) {
                            $attendence = Attendance::where("date", $date)
                            ->where("employee_code", $emp_code)
                            ->first();
                            return response(['flag'=>1,'status'=>200, 'message'=>'Attendence logout success','todayLogin'=>false,'isHoliday'=>false, 'todayLogout'=>true,'data'=>$attendence]);
                            // return Helper::rj("Attendence logout success", 1,$attendence);
                        } else {
                            return response(['flag'=>0, 'message'=>'somthing is wrong','todayLogin'=>false]);
                        }
                    }
                    } else {
                        // $todayLogin=['todayLogin'=>'false'];
                        return response(['flag'=>0, 'message'=>'Today is holiday','isHoliday'=>true,'todayLogin'=>false]);
                        // return Helper::rj("Today is holiday", 0,$todayLogin);
                    }
        } catch (Exception $e) {
            return Helper::rj("Server Error.", 500);
        }
    }

//attendence list
public function attendenceList(Request $request){
    try{
        $user = Auth::guard("api")->user();
        $emp_code=$request->emp_id;
        $startDate=[];
        $endDate=[];
        $lastDate=[];

        if(request()->query('gm')){

            $selectedMonth = Carbon::parse(request()->query('gm'))->format('m'); // Extract month from the given date
            $startOfMonth = Carbon::now()->month($selectedMonth)->startOfMonth();
            $endOfMonth = Carbon::now()->month($selectedMonth)->endOfMonth();

            $startDate[]=$startOfMonth->format('Y-m-d');
            $endDate[]=$endOfMonth->format('Y-m-d');
            $lastDate[]=$endOfMonth->format('d');
        }else{
           $startDate[]=Carbon::now()->format('Y-m-01');
           $endDate[]=Carbon::now()->format('Y-m-t');
           $lastDate[]=Carbon::now()->format('t');
        }
         $startDateString=implode(',', $startDate);
         $endDateString=implode(',', $endDate);
         //count holyday
         $lastDateString=implode(',', $lastDate);
        //  dd($lastDateString);


         if($user->user_type==="employee"){

            $data = Attendance::whereBetween('date', [$startDateString, $endDateString])
            ->where('employee_code', $emp_code)
            ->get();

            $halfDayData = Attendance::whereBetween('date', [$startDateString, $endDateString])
            ->where('employee_code', $emp_code)
            ->where('duty_hours', '<=', '5')
            ->get();
            $halfDayCount = $halfDayData->count();


            $fullDayData = Attendance::whereBetween('date', [$startDateString, $endDateString])
            ->where('employee_code', $emp_code)
            ->where('duty_hours', '>=', '5')
            ->get();
            $fullDayCount = $fullDayData->count();

            $totakWorkingDay=22;
            if(sizeof($data)){
                return response([
                    'flag' => 1,
                    'status' => 200,
                    'message' => 'Attendance list',
                    'response' => [
                        'report' => [
                            'fullDay' => $fullDayCount,
                            'halfDay' => $halfDayCount,
                            'totalWorkingDay' => 22,
                            'absent' => 4,
                        ],
                        'data' => $data,
                    ],
                ]);

            }else{
                return response([
                    'flag' => 0,
                    'status' => 400,
                    'message' => 'Not Found Attendance list',
                    'response' => [
                        'report' => [
                            'fullDay' => $fullDayCount,
                            'halfDay' => $halfDayCount,
                            'totalWorkingDay' => 0,
                            'absent' => 0,
                        ],
                        'data' => $data,
                    ],
                ]);
            }


         }else{

         }
    }catch(Exception $e){
        return Helper::rj("Server Error.", 500);
    }
}

public function attendenceGraph(Request $request){

    try{
        $user = Auth::guard("api")->user();
        $emp_code=$request->emp_id;
        $emp=DB::table('employee')->where('emp_code',$emp_code)->first();
        $currentYear = date('Y');
        $result = DB::table('attandence')
                    ->select(
                        DB::raw('SUM(duty_hours) as total_actual_hour'),
                        DB::raw('(CASE
                                    WHEN MONTH(date) IN (4, 6, 9, 11) THEN 23
                                    WHEN MONTH(date) = 2 THEN 20
                                    ELSE 22
                                END * 8) as total_static_hour'),
                        DB::raw('MONTH(date) as month')
                    )
                    ->where('employee_code', $emp_code)
                    ->whereYear('date', $currentYear)
                    ->groupBy(DB::raw('MONTH(date)'))
                    ->get();
         if(sizeof($result)){
        return response(array('flag'=>1, 'status'=>200,'message'=>'Attendence Graph List','response' => [
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
