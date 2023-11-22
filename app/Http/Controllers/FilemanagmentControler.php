<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use view;
use Validator;
use Session;
use DB;
use Illuminate\Support\Facades\Input;
use Auth;
use Exception;
use App\Models\Registration;
use App\Models\fileManager;
use App\Models\Employee;
use App\Models\UserModel;
use App\Models\fileDivision;
use Carbon\Carbon;
class FilemanagmentControler extends Controller
{
 public function fileManagmentList(){
  $data['file_details']=fileManager::get();
    return View("filemanagment/file-manager-list",$data);
 }  
 public function fileManagmentView(){
   $email = Session::get('emp_email');
   $data["emp_record"]=UserModel::where("email",$email)->first();
   $empid= $data["emp_record"]->employee_id;
   $data["emp_details"]=Employee::where("emid",$empid)->first();
   return View("filemanagment/add-fileManagment",$data);
 }
 public function savefilemanagment(Request $request){
   $email = Session::get('emp_email');
   $date=carbon::now()->toDateString();
   // dd($date);
   if(!empty($email)){
      $arryValue=array(
         "file_name"=>$request->file_name,
         "emp_id"=>$request->emp_id,
         "organization_id"=>$request->organization_id,
         "status"=>$request->status,
         "remarks"=>$request->remarks,
         "created_at"=>$date,
         "update_at"=>$date,
         "delete_at"=>$date,
      );
      fileManager::insert($arryValue);
      return redirect("fileManagment/fileManagmentList");
      Session::flash('message', 'File Manager Successfully Save.');
   }else{
      return redirect("");
   }
 }
 public function fileManagmentViewedit($id){
   $data['file_details']=fileManager::where("id",$id)->first();
   return View("filemanagment/edit-fileManagment",$data);
 }
 public function savefilemanagmentupdate(Request $request){
   $date=carbon::now()->toDateString();
   $arryValue=array(
      "file_name"=>$request->file_name,
      "emp_id"=>$request->emp_id,
      "organization_id"=>$request->organization_id,
      "status"=>$request->status,
      "remarks"=>$request->remarks,
      "update_at"=>$date,
     
   );
   DB::table('file_managers')->where('id',$request->id)->update($arryValue);
   return redirect("fileManagment/fileManagmentList");
   Session::flash('message', 'File Manager Successfully Update.');
 }

 public function filedivisionlist(){
   $data['file_details']= fileDivision::get();
   return view("fileManagment/devision",$data);
 }
 public function filedivisionView(){
   $email = Session::get('emp_email');

   $data["emp_record"]=UserModel::where("email",$email)->first();
   $empid= $data["emp_record"]->employee_id;
   $data["emp_details"]=Employee::where("emid",$empid)->first();
   $data["file_details"]=fileManager::get();
   return view("fileManagment/file-devision-Add",$data);
 }
 public function filedivisionadd(Request $request){
   $date=carbon::now()->toDateString();
   $email = Session::get('emp_email');
   if(!empty($email)){
      $arryValue=array(
         "name"=>$request->name,
         "organization_id"=>$request->organization_id,
         "emp_id"=>$request->emp_id,
         "division"=>$request->division,
         "emp_name"=>$request->emp_name,
         "file_name"=>$request->file_name,
         "status"=>$request->status,
         "created_at"=>$date,
         "updated_at"=>$date,
         "deleted_at"=>$date,
      );
      fileDivision::insert($arryValue);
      return redirect("fileManagment/file-devision-list");
      Session::flash('message', 'File Division Successfully Insert.');
      
   }else{
      return redirect("");
   }
 }
 public function filedivisionViewedit($id){
  $data['file_details']= fileDivision::where("id",$id)->first();
  return view("fileManagment/file-devision-edit",$data);
 }
 public function filedivisionViewupdate(Request $request){
   $email = Session::get('emp_email');
   $date=carbon::now()->toDateString();
   if(!empty($email)){
      $arryValue=array(
         "name"=>$request->name,
         "organization_id"=>$request->organization_id,
         "emp_id"=>$request->emp_id,
         "division"=>$request->division,
         "emp_name"=>$request->emp_name,
         "file_name"=>$request->file_name,
         "status"=>$request->status,
         "updated_at"=>$date,
      );
      DB::table('file_devisions')->where('id',$request->id)->update($arryValue);
      return redirect("fileManagment/file-devision-list");
      Session::flash('message', 'File Division Successfully Update.');
   }else{
      Session::flash('message', 'File Division Tecnical Issue');
   }
 }
}
