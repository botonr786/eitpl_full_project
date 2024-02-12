<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\announcement;
use Illuminate\Support\Facades\Auth;
use DB;
use Carbon\Carbon;

class AnnouncementController extends Controller
{
    
    public function getAnnouncementList(Request $request){
        
        try{

            $user = Auth::guard("api")->user();
            $currentDate = now();
            $emp_code=$request->emp_id;
            $org_id=$request->emid;
            $result=announcement::select(
                'announcement_title', 
                'announcement_desc',
                'image'
            )
            ->selectRaw("IFNULL(announcement_title, '') as announcement_title")
            ->selectRaw("IFNULL(announcement_desc, '') as announcement_desc")
            ->selectRaw("IFNULL(image, '') as image")
            ->where('status', '=', 1)
            ->where('from_date', '<=', $currentDate)
                    ->where('to_date', '>=', $currentDate)
            ->where('emid', '=', $org_id)
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

        }
        catch(Exception $e){
            return Helper::rj("Server Error.", 500);
        }

    }
}