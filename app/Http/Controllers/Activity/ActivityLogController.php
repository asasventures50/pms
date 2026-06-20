<?php

namespace App\Http\Controllers\Activity;

use App\Http\Controllers\Controller;
use App\Models\Activity\ActivityLog;
use App\Models\User;
use App\Services\Activity\ActivityLogFilter;
use App\Services\Activity\ActivityLogReportBuilder;
use App\Support\TableSort;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function __construct(
        protected ActivityLogFilter $filter,
        protected ActivityLogReportBuilder $reportBuilder,
    ) {}

    public function index(Request $request): View
    {
        $perPage = max(1, min(100, (int) $request->query('per_page', 25)));
        $sort = TableSort::resolve($request, ['created_at', 'action'], 'created_at', 'desc');

        $query = $this->filter->apply($request);
        $query->orderBy($sort['column'], $sort['direction'])->orderByDesc('id');

        return view('activity.logs.index', [
            'logs' => $query->paginate($perPage)->appends($request->query()),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'actions' => ActivityLog::query()
                ->select('action')
                ->distinct()
                ->orderBy('action')
                ->pluck('action'),
            'sortColumn' => $sort['column'],
            'sortDirection' => $sort['direction'],
        ]);
    }

    public function report(Request $request): View
    {
        $filters = $this->filter->validatedFilters($request);
        $logs = $this->filter->apply($request)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        return view('activity.logs.report', [
            'filters' => $filters,
            'filterSummary' => $this->filter->summary($filters),
            'totalEvents' => $logs->count(),
            'userReports' => $this->reportBuilder->build($logs),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'actions' => ActivityLog::query()
                ->select('action')
                ->distinct()
                ->orderBy('action')
                ->pluck('action'),
        ]);
    }

    public function show(ActivityLog $activityLog): View
    {
        $activityLog->load('user');

        return view('activity.logs.show', [
            'log' => $activityLog,
        ]);
    }
}
