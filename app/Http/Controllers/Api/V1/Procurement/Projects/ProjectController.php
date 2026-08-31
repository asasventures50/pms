<?php

namespace App\Http\Controllers\Api\V1\Procurement\Projects;

use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\Projects\StoreProjectRequest;
use App\Http\Requests\Procurement\Projects\UpdateProjectRequest;
use App\Http\Resources\Api\V1\Procurement\Projects\ProjectResource;
use App\Models\Procurement\Projects\Project;
use App\Services\Procurement\Projects\ProjectCatalogService;
use App\Services\Procurement\Projects\ProjectCodeGenerator;
use App\Support\TableSort;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function __construct(
        protected ProjectCatalogService $catalogService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = max(1, min(100, (int) $request->query('per_page', 15)));

        $sort = TableSort::resolve($request, ['code', 'name', 'created_at'], 'name', 'asc');

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

        return ProjectResource::collection($query->paginate($perPage)->withQueryString())->additional([
            'statuses' => ['active', 'inactive'],
        ]);
    }

    public function store(StoreProjectRequest $request): JsonResponse
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

        $project->loadCount('zones');
        $project->load(['zones' => fn ($q) => $q->orderBy('code')]);

        return (new ProjectResource($project))
            ->additional(['message' => 'Project created successfully.'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Project $project): ProjectResource
    {
        $project->loadCount('zones');
        $project->load(['zones' => fn ($q) => $q->orderBy('code')]);

        return new ProjectResource($project);
    }

    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $project) {
            $project->update([
                'name' => $data['name'],
                'status' => $data['status'],
            ]);

            $this->catalogService->syncZones($project, $data['zones'] ?? []);
        });

        $project->refresh();
        $project->loadCount('zones');
        $project->load(['zones' => fn ($q) => $q->orderBy('code')]);

        return (new ProjectResource($project))
            ->additional(['message' => 'Project updated successfully.'])
            ->response();
    }

    public function destroy(Project $project): JsonResponse
    {
        $this->catalogService->softDeleteProjectCascade($project);

        return response()->json([
            'message' => 'Project deleted successfully.',
        ]);
    }
}
