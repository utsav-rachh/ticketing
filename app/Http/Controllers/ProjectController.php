<?php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Project::class);

        $status = $request->input('status');
        $projects = Project::with(['owner','creator'])
            ->withCount('tickets')
            ->when(in_array($status, ['active','on_hold','completed']), fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('projects.index', compact('projects','status'));
    }

    public function create()
    {
        $this->authorize('create', Project::class);
        $owners = $this->managementOwners();
        return view('projects.create', compact('owners'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Project::class);
        $data = $this->validated($request);

        $project = Project::create($data + [
            'number'     => Project::generateNumber(),
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project created: ' . $project->number);
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);

        $project->load(['owner','creator']);
        $tickets = $project->tickets()
            ->with(['creator','category','subcategory','assignee','branch.region'])
            ->latest()
            ->paginate(25);

        return view('projects.show', compact('project','tickets'));
    }

    public function edit(Project $project)
    {
        $this->authorize('update', $project);
        $owners = $this->managementOwners();
        return view('projects.edit', compact('project','owners'));
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);
        $data = $this->validated($request);
        $project->update($data);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project updated.');
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project archived.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'        => 'required|string|max:200',
            'description' => 'nullable|string|max:5000',
            'owner_id'    => 'required|exists:users,id',
            'status'      => 'required|in:active,on_hold,completed',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
        ]);
    }

    private function managementOwners()
    {
        return User::where('role', 'management')->where('is_active', true)->orderBy('name')->get(['id','name']);
    }
}
