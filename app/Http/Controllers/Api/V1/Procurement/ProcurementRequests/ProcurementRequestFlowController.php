<?php

namespace App\Http\Controllers\Api\V1\Procurement\ProcurementRequests;

use App\Enums\Procurement\Flow\FlowStageKey;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Procurement\Flow\ProcurementRequestFlowCollection;
use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Services\Procurement\Flow\ProcurementRequestFlowBuilder;
use Illuminate\Http\Request;

class ProcurementRequestFlowController extends Controller
{
    public function index(Request $request, ProcurementRequestFlowBuilder $builder): ProcurementRequestFlowCollection
    {
        $user = $request->user();

        abort_unless($user !== null && $user->canAccessProcurementRequestFlow(), 403);

        $viewAll = $user->canViewAllProcurementRequestFlows();
        $perPage = max(1, min(50, (int) $request->query('per_page', 15)));

        $query = ProcurementRequest::query()
            ->with(['creator:id,name', 'project:id,code,name'])
            ->latest();

        if ($user->scopesProcurementRequestFlowToOwn()) {
            $query->where('created_by', $user->id);
        }

        $procurementRequests = $query
            ->paginate($perPage)
            ->withQueryString();

        $flows = $builder->buildMany($procurementRequests->getCollection());

        return (new ProcurementRequestFlowCollection($procurementRequests, $flows, $viewAll))
            ->additional([
                'view_all' => $viewAll,
                'stage_keys' => array_map(
                    static fn (FlowStageKey $key) => $key->value,
                    FlowStageKey::ordered(),
                ),
            ]);
    }
}
