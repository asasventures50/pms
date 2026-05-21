<?php

namespace App\Http\Controllers\Procurement\Projects;

use App\Http\Controllers\Controller;
use App\Models\Procurement\Projects\Project;
use App\Models\Procurement\Projects\Zone;
use App\Services\Procurement\Projects\ZoneCodeGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ZoneQuickStoreController extends Controller
{
    public function quickStore(Request $request): JsonResponse
    {
        if (! $request->user()?->hasPermission('projects.update')) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $project = Project::query()->findOrFail((int) $request->input('project_id'));

        $zone = Zone::query()->create([
            'project_id' => $project->id,
            'code' => app(ZoneCodeGenerator::class)->nextForProject($project->id),
            'name' => trim((string) $request->input('name')),
            'status' => 'active',
        ]);

        return response()->json([
            'id' => $zone->id,
            'code' => $zone->code,
            'name' => $zone->name,
            'project_id' => $zone->project_id,
        ], 201);
    }
}
