<?php

namespace App\Http\Controllers\Procurement\Projects;

use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\Projects\StoreProjectRequest;
use App\Http\Requests\Procurement\Projects\UpdateProjectRequest;
use App\Models\Procurement\Projects\Project;
use App\Services\Procurement\Projects\ProjectCatalogService;
use App\Services\Procurement\Projects\ProjectCodeGenerator;
use App\Support\TableSort;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct(
        protected ProjectCatalogService $catalogService
    ) {}

    public function index(Request $request): View
    {
        $perPage = max(1, min(100, (int) $request->query('per_page', 15)));

        $allowedSorts = ['code', 'name', 'created_at'];
        $sort = TableSort::resolve($request, $allowedSorts, 'name', 'asc');

        $query = Project::query()->withCount('zones');

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(function ($q) use ($term) {
                $q->where('code', 'like', $term)
                    ->orWhere('name', 'like', $term);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $query->orderBy($sort['column'], $sort['direction'])
            ->orderBy('id');

        $projects = $query->paginate($perPage)->appends($request->query());

        return view('procurement.projects.index', [
            'projects' => $projects,
            'sortColumn' => $sort['column'],
            'sortDirection' => $sort['direction'],
        ]);
    }

    public function create(): View
    {
        return view('procurement.projects.create', [
            'project' => new Project(['status' => 'active']),
            'nextProjectCode' => app(ProjectCodeGenerator::class)->next(),
        ]);
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $project = DB::transaction(function () use ($data) {
            $project = Project::query()->create([
                'code' => app(ProjectCodeGenerator::class)->next(),
                'name' => $data['name'],
                'status' => $data['status'],
            ]);

            $this->catalogService->syncZones($project, $data['zones'] ?? []);

            return $project;
        });

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    public function show(Project $project): View
    {
        $project->load(['zones' => fn ($q) => $q->orderBy('code')]);

        return view('procurement.projects.show', [
            'project' => $project,
        ]);
    }

    public function edit(Project $project): View
    {
        $project->load(['zones' => fn ($q) => $q->orderBy('code')]);

        return view('procurement.projects.edit', [
            'project' => $project,
        ]);
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $project) {
            $project->update([
                'name' => $data['name'],
                'status' => $data['status'],
            ]);

            $this->catalogService->syncZones($project, $data['zones'] ?? []);
        });

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->catalogService->softDeleteProjectCascade($project);

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }
}
