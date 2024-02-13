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
	Route::post('listAttendence','App\Http\Controllers\Api\AttentenceController@attendenceList');
	Route::post('graphAttendence','App\Http\Controllers\Api\AttentenceController@attendenceGraph');

    //Leave Route
    Route::post('leave-type','App\Http\Controllers\Api\LeaveController@leaveType');
    Route::post('leave-list','App\Http\Controllers\Api\LeaveController@leaveList');
    Route::post('leave-apply','App\Http\Controllers\Api\LeaveController@leaveApply');

    //Payroll Route
    Route::post('payroll-list','App\Http\Controllers\Api\PayrollController@payrollList');
    //holiday
    Route::post('emp-holiday-list','App\Http\Controllers\Api\HolidayController@employeeHolidays');

    //birthday list
    Route::post('birthday-list','App\Http\Controllers\Api\BirthdayReminderController@getBirthdayList');

    //task list
    Route::post('task-list','App\Http\Controllers\Api\TaskController@getTaskList');
    Route::post('task-dashbord','App\Http\Controllers\Api\TaskController@dashbordTaskmanager');
    Route::post('task-update','App\Http\Controllers\Api\TaskController@updateTask');
    Route::post('task-count','App\Http\Controllers\Api\TaskController@taskStatusCountsForUser');

    //announcement list
    Route::post('announcement-list','App\Http\Controllers\Api\AnnouncementController@getAnnouncementList');



});




