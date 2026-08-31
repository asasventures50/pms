<?php

namespace App\Http\Controllers\Api\V1\Procurement\Projects;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Projects\Project;
use App\Services\Procurement\Projects\ProjectCodeGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectQuickStoreController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        if (! $request->user()?->hasPermission('projects.create')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $project = Project::query()->create([
            'code' => app(ProjectCodeGenerator::class)->next(),
            'name' => trim((string) $request->input('name')),
            'status' => 'active',
        ]);

        return response()->json([
            'id' => $project->id,
            'code' => $project->code,
            'name' => $project->name,
        ], 201);
    }
}
