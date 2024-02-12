<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TaskManagement\MasterLabels;
use App\Models\TaskManagement\Project;
use App\Models\TaskManagement\Task;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Barryvdh\DomPDF\Facade\Pdf;
use Validator;
use DB;
Use \Carbon\Carbon;

class TaskController extends Controller
{
    public function dashbordTaskmanager(Request $request){
        try{
            $user = Auth::guard("api")->user();
            $currentUserType=$user->user_type;
            $currentUser=$request->userId;
            // dd($currentUser);
            if ($currentUserType === 'employer') {
                $projects = Project::select('projects.*',  'u.name as owner')
                    ->leftJoin('users as u', 'u.id', '=', 'projects.createdBy')
                    ->get();
                foreach ($projects as $k => $p) {
                    // echo $p->id;

                    $labels = MasterLabels::where('project_id', $p->id)->get();
                    if (count($labels) > 0) {
                        $tempLeb = [];
                        foreach ($labels as $kk => $l) {
                            $openCount = Task::where(['project_id' => $p->id, 'status' => $l->title])->count();
                            // $tempProjects[$k]['labels'][$kk] = [
                            //     'title' => $l->title,
                            //     'count' => $openCount
                            // ];

                            // print_r($openCount);
                            array_push($tempLeb, [
                                'title' => $l->title,
                                'count' => $openCount
                            ]);
                        }
                        // $tempPro = array_merge((array)$p, ['labels' => $tempLeb]);
                        // array_push($tempProjects, $tempPro);
                        $p->setAttribute('labels', (object)$tempLeb);
                    } else {
                        // $temp = [];
                        // $tempProjects[$k]['labels'] = [];
                        $p->setAttribute('labels', (object)[]);
                    }
                }
                $data['projects'] =  $projects;
            } else {
                $empDetails = User::select("users.*", 'e.id as emp_id')
                    ->leftJoin('employee as e', 'e.emp_code', '=', 'users.employee_id')
                    ->where('users.id',$currentUser)
                    ->first();
                    // dd($empDetails);
                $projects = Project::select('projects.*',  'u.name as owner')
                    ->where('projects.createdBy', $currentUser)
                    ->orWhere('pm.user_id', $empDetails->emp_id)
                    ->leftJoin('project_members as pm', 'pm.project_id', '=', 'projects.id')
                    ->leftJoin('users as u', 'u.id', '=', 'projects.createdBy')
                    ->get();
                $tempProjects = (array)$projects;
                foreach ($projects as $k => $p) {
                    // echo $p->id;

                    $labels = MasterLabels::where('project_id', $p->id)->get();
                    if (count($labels) > 0) {
                        $tempLeb = [];
                        foreach ($labels as $kk => $l) {
                            $openCount = Task::where(['project_id' => $p->id, 'status' => $l->title])->count();
                            // $tempProjects[$k]['labels'][$kk] = [
                            //     'title' => $l->title,
                            //     'count' => $openCount
                            // ];

                            // print_r($openCount);
                            array_push($tempLeb, [
                                'title' => $l->title,
                                'count' => $openCount
                            ]);
                        }
                        // $tempPro = array_merge((array)$p, ['labels' => $tempLeb]);
                        // array_push($tempProjects, $tempPro);
                        $p->setAttribute('list', (object)$tempLeb);
                    } else {
                        // $temp = [];
                        // $tempProjects[$k]['labels'] = [];
                        $p->setAttribute('list', (object)[]);
                    }
                }
                return response(['flag'=>1,'status'=>200,'message'=>'Task Manager List','data'=>$projects]);

            }
        }catch(Exception $e){
            return Helper::rj("Server Error.", 500);
        }
    }

    public function taskAssignmentEmployee(Request $request){
        try{
            $user = Auth::guard("api")->user();
            $projectId=$request->projectId;
            $employeeId=$request->emp_id;
            if($projectId !==null && $employeeId !==null){
                // $result = Task::selectRaw('COUNT(id) as counttype, status')
                //     ->where('project_id', $projectId)
                //     ->where('assignedTo', $employeeId)
                //     ->groupBy('status')
                //     ->get();
                $projectId = 5;
                $employeeId = 22;
                
                $result = Task::select('status', 'task_desc')
                    ->where('project_id', $projectId)
                    ->where('assignedTo', $employeeId)
                    ->groupBy('status')
                    ->get();

                    dd($result);
                
                // Map each status to an array of corresponding project descriptions
                $output = $result->map(function ($item) {
                    return [
                        'status' => $item->status,
                        'task_desc' => $item->pluck('task_desc')->toArray(),
                    ];
                });
                
                // $output now contains an array where each status is paired with an array of corresponding project descriptions
                return response(['flag' => 1, 'status' => 200, 'message' => 'Work Report List', 'data' => $output]);
                
            
            }else{
                return response(array('flag'=>0,'status'=>400,'message'=>"Validation Issue please Required feild filap first"));
            }
        }catch(Exception $e){
            return Helper::rj("Server Error.", 500);
        }
    }

}
