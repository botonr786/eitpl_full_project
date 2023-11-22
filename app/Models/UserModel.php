<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use DB;

class UserModel extends Model
{
    use HasApiTokens;
    use HasFactory;
    protected $table="users";

    public function userfind($email,$password){
      $data=DB::table('users')->where("email",$email)
      ->where("password",$password)
      ->where("status", "=", "active")
      ->where("user_type", "!=", "admin")
      ->first();
      return $data;
    }
}
