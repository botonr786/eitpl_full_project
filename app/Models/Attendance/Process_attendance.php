<?php

namespace App\Models\Attendance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;
class Process_attendance extends Model
{
    use HasFactory;
    protected $table= "attandence";

    public function holidayValidation($date){
        try{
         $data=DB::table('holiday')->where('from_date',$date)->get();
         return $data;
        }catch(Exception $e){
            return reponse()->json("server error");
        }
    }

    public function dayWiseCheckAttentence($emp_code,$date){
        try{
          $data=DB::table('attandence')
            ->where('employee_code',$emp_code)
            ->where('date',$date)
            ->get();
            // dd()
           return $data;
        }catch(Exception $e){
            return reponse()->json("server error");
        }
    }

    public function checkEmployee($emp_code){
        try{
        $data=DB::table('employee')->where('emp_code',$emp_code)->first();
        return $data;
    }catch(Exception $e){
        return reponse()->json("server error");
    }
    }
}
