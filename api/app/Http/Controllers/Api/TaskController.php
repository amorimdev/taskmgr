<?php

namespace App\Http\Controllers\Api;

use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;

class TaskController extends Controller
{
    public $user = null;

    public function __construct(Request $request)
    {
        $this->user = $request->current_user;
    }

    public function index(Request $request)
    {
        return response()->json([
            'tasks' => Task::with('project')->fromUser($this->user)->get()
        ], 200);
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'description'  => 'required|max:255',
            'project_id'  => 'required|integer',
        ]);

        $project = $this->findProjectOrFail($request->input('project_id'));

        $task = new Task($request->only('description'));
        $task->project()->associate($project);
        $task->save();

        return response()->json([
            'message' => 'Task successfully created',
            'task' => $task
        ], 200);
    }

    public function show(Request $request, $id)
    {
        $task = $this->findTaskOrFail($id, false)
            ->makeHidden('project_id')
            ->load('project')
        ;

        return response()->json([
            'task' => $task
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $task = $this->findTaskOrFail($id);

        $this->validate($request, [
            'description'  => 'required|max:255',
            'project_id'  => 'required|integer'
        ]);

        $project = $this->findProjectOrFail($request->input('project_id'));
        $task->project()->associate($project);
        $task->fill($request->only('description'));
        $task->save();

        return response()->json([
            'task' => $task,
            'message' => 'Task successfully updated'
        ], 200);
    }

    public function close(Request $request, $id)
    {
        $task = $this->findTaskOrFail($id);

        $task->finished_at = \Carbon\Carbon::now();
        $task->save();

        return response()->json([
            'task' => $task,
            'message' => 'Task successfully closed'
        ], 200);
    }

    public function delete(Request $request, $id)
    {
        $task = $this->findTaskOrFail($id);
        $task->delete();

        return response()->json([
            'message' => 'Task successfully deleted'
        ], 200);
    }

    protected function findProjectOrFail($id) {
        return Project::fromUser($this->user)->findOrFail($id);
    }

    protected function findTaskOrFail($id, $openTaskRequired = true)
    {
        $task = Task::fromUser($this->user)->findOrFail($id);
        if ($openTaskRequired && $task->isClosed()) {
            throw new Exception('Operantion requires an open task');
        }
        return $task;
    }
}
