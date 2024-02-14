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
use App\Models\TaskManagement\TaskComment;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Barryvdh\DomPDF\Facade\Pdf;
use Validator;
use DB;
Use \Carbon\Carbon;
use \Illuminate\Auth\AuthenticationException;

class TaskController extends Controller
{
    public function dashbordTaskmanager(Request $request)
{
    try {
        $user = Auth::guard("api")->user();
        $currentUserType = $user->user_type;
        $currentUser = $request->userId;

        if ($currentUserType === 'employer') {
            $projects = Project::select('projects.*', 'u.name as owner')
                ->leftJoin('users as u', 'u.id', '=', 'projects.createdBy')
                ->get();

            foreach ($projects as $k => $p) {
                $labels = MasterLabels::where('project_id', $p->id)->get();
                if (count($labels) > 0) {
                    $tempLeb = [];
                    foreach ($labels as $kk => $l) {
                        $openCount = Task::where(['project_id' => $p->id, 'status' => $l->title])->count();
                        array_push($tempLeb, [
                            'title' => $l->title,
                            'count' => $openCount
                        ]);
                    }
                    $p->setAttribute('labels', (object)$tempLeb);
                } else {
                    $p->setAttribute('labels', (object)[]);
                }
            }

            $data['projects'] = $projects;

        } else {
            $empDetails = User::select("users.*", 'e.id as emp_id')
                ->leftJoin('employee as e', 'e.emp_code', '=', 'users.employee_id')
                ->where('users.id', $currentUser)
                ->first();

            $projects = Project::select('projects.*', 'u.name as owner')
                ->where('projects.createdBy', $currentUser)
                ->orWhere('pm.user_id', $empDetails->emp_id)
                ->leftJoin('project_members as pm', 'pm.project_id', '=', 'projects.id')
                ->leftJoin('users as u', 'u.id', '=', 'projects.createdBy')
                ->get();

            foreach ($projects as $k => $p) {
                $labels = MasterLabels::where('project_id', $p->id)->get();
                if (count($labels) > 0) {
                    $tempLeb = [];
                    foreach ($labels as $kk => $l) {
                        $openCount = Task::where(['project_id' => $p->id, 'status' => $l->title])->count();
                        array_push($tempLeb, [
                            'title' => $l->title,
                            'count' => $openCount
                        ]);
                    }
                    $p->setAttribute('list', (object)$tempLeb);
                } else {
                    $p->setAttribute('list', (object)[]);
                }
            }

            // Re-index the array numerically
            $projects = $projects->values();

            return response(['flag' => 1, 'status' => 200, 'message' => 'Task Manager List', 'data' => $projects]);
        }
    } catch (AuthenticationException $e) {
        return Helper::rj("Server Error.", 500);
    }
}


    public function getTaskList(Request $request){
        try{
            $user = Auth::guard("api")->user();
            $projectId=$request->projectId;
            $employeeId=$request->emp_id;
            $status = $request->status;
            if($projectId !==null && $employeeId !==null){
                $result = Task::where('project_id', $projectId)
                ->where('assignedTo', $employeeId)
                ->where('status', $status)
                ->get();
                return response(['flag' => 1, 'status' => 200, 'message' => 'Work Report List', 'data' => $result]);
            }else{
                return response(array('flag'=>0,'status'=>400,'message'=>"Validation Issue please Required feild filap first"));
            }
        }catch(AuthenticationException $e){
            return Helper::rj("Server Error.", 500);
        }
    }
    public function updateTask(Request $request){
        try{
            $user = Auth::guard("api")->user();
            $status = $request->status;
            $user_id = $request->user_id;
            $comment = $request->comment;
            $task_id = $request->task_id;

             // Update Task Status
            $updatedTaskCount = Task::where('id', $task_id)->update([
                'status' => $status,
                'updated_at' => now(),
            ]);

            // Insert Task Comment
            TaskComment::create([
                'createdBy' => $user_id,
                'task_id' => $task_id,
                'comment_details' => $comment,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($updatedTaskCount > 0) {
                return response(['flag' => 1, 'status' => 200, 'message' => 'Task updated successfully.']);
            } else {
                return response(['flag' => 0, 'status' => 500, 'message' => 'Task update failed.']);
            }

        }catch(AuthenticationException $e){
            return Helper::rj("Server Error.", 500);
        }
    }


    public function taskStatusCountsForUser(Request $request)
    {
        try{
            $user = Auth::guard("api")->user();
            $employee_id = $request->employee_id;
            // Assuming you have 'tasks' and 'projects' tables with proper relationships
            $statusCounts = Task::select(
                'projects.id as project_id',
                'projects.createdBy as created_by',
                'users.name as created_by_name', // Include the user name
                'projects.created_at',
                'projects.title as project_name',
                'tasks.status',
                DB::raw('COUNT(tasks.id) as count')
            )
                ->join('projects', 'tasks.project_id', '=', 'projects.id')
                ->leftJoin('users', 'users.id', '=', 'projects.createdBy') // Join with users table to get the user name
                ->where('tasks.assignedTo', $employee_id)
                ->groupBy('projects.title', 'tasks.status')
                ->get();

            // Organize the data in a nested structure
            $formattedData = [];

            foreach ($statusCounts as $statusCount) {
                $projectId = $statusCount->project_id;
                $createdBy = $statusCount->created_by;
                $createdByName = $statusCount->created_by_name; // Access the user name
                $createdAt = $statusCount->created_at;
                $projectName = $statusCount->project_name;
                $status = $statusCount->status;
                $count = $statusCount->count;

                // Create or update the project in the formatted data array
                if (!isset($formattedData[$projectId])) {
                    $formattedData[$projectId] = [
                        'project_id' => $projectId,
                        'created_by' => $createdBy,
                        'created_by_name' => $createdByName, // Include the user name
                        'created_at' => $createdAt,
                        'project_name' => $projectName,
                        'tasks' => [],
                    ];
                }

                // Add the task details to the tasks array using the status as the key
                $formattedData[$projectId]['tasks'][] = [
                    'title' => $status,
                    'count' => $count,
                ];
            }

            // Convert the 'tasks' array to a numerically indexed array
            foreach ($formattedData as &$projectData) {
                $projectData['tasks'] = array_values($projectData['tasks']);
            }

            return response(['flag' => 1, 'status' => 200, 'message' => 'Task status counts for user', 'data' => array_values($formattedData)]);
        }catch(AuthenticationException $e){
            return Helper::rj("Server Error.", 500);
        }
    }
    public function taskLabel(Request $request){
        try {
            $user = Auth::guard("api")->user();
            $employee_id = $request->employee_id;
            $project_id = $request->project_id;
            $result = MasterLabels::where('project_id', $project_id)->get();
            if(count($result) > 0){
            return response(['flag' => 1, 'status' => 200, 'message' => 'Work Report List', 'data' => $result]);
            }else{
                return response(array('flag'=>0,'status'=>400,'message'=>"Validation Issue please Required feild filap first"));
            }

        }catch(AuthenticationException $e){
            return Helper::rj("Server Error.", 500);
        }

    }
}
