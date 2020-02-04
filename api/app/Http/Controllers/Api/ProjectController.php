<?php

namespace App\Http\Controllers\Api;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProjectController extends Controller
{
    public $user = null;

    public function __construct(Request $request)
    {
        $this->user = $request->current_user;
    }

    public function index(Request $request)
    {
        return response()->json([
            'projects' => $this->user->projects
        ], 200);
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'name'  => 'required',
        ]);

        $project = new Project($request->only('name'));
        $project->user()->associate($this->user);
        $project->save();

        return response()->json([
            'message' => 'Project successfully created',
            'project' => $project
        ], 200);
    }

    public function show(Request $request, $id)
    {
        $project = $this->findOrFail($id);
        $project->load('tasks');

        return response()->json([
            'project' => $project
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $project = $this->findOrFail($id);

        $this->validate($request, [
            'name'  => 'required',
        ]);

        $project->fill($request->only('name'));
        $project->save();

        return response()->json([
            'message' => 'Project successfully updated',
            'project' => $project
        ], 200);
    }

    public function delete(Request $request, $id)
    {
        $project = $this->findOrFail($id);
        $project->delete();

        return response()->json([
            'message' => 'Project successfully deleted',
        ], 200);
    }

    protected function findOrFail($id) {
        return Project::fromUser($this->user)->findOrFail($id);
    }
}
