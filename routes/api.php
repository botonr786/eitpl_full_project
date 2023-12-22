<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'v1/', 'middleware' => ['api']], function () {
	Route::post('login', 'App\Http\Controllers\Api\LoginController@doLogin');
});
Route::group(['middleware' => ['auth:api']], function () {
    //Attendance Route
	Route::post('attentences','App\Http\Controllers\Api\AttentenceController@attentenceAdd');
	Route::get('listAttendence','App\Http\Controllers\Api\AttentenceController@attendenceList');
	Route::post('graphAttendence','App\Http\Controllers\Api\AttentenceController@attendenceGraph');

    //Leave Route
    Route::post('leave-type','App\Http\Controllers\Api\LeaveController@leaveType');
    Route::post('leave-list','App\Http\Controllers\Api\LeaveController@leaveList');
    Route::post('leave-apply','App\Http\Controllers\Api\LeaveController@leaveApply');

    //Payroll Route
    Route::post('payroll-list','App\Http\Controllers\Api\PayrollController@payrollList');

});




