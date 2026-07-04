<?php

namespace App\Http\Controllers\Procurement\ProcurementRequests;

use App\Http\Controllers\Controller;
use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Services\Procurement\Flow\ProcurementRequestFlowBuilder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProcurementRequestFlowController extends Controller
{
    public function index(Request $request, ProcurementRequestFlowBuilder $builder): View
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

        return view('procurement.procurement-requests.my-flow', [
            'procurementRequests' => $procurementRequests,
            'flows' => $flows,
            'viewAll' => $viewAll,
        ]);
    }
}
