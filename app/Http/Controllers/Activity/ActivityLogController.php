<?php

namespace App\Http\Controllers\Activity;

use App\Http\Controllers\Controller;
use App\Models\Activity\ActivityLog;
use App\Models\User;
use App\Services\Activity\ActivityLogFilter;
use App\Services\Activity\ActivityLogInsights;
use App\Services\Activity\ActivityLogReportBuilder;
use App\Support\TableSort;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function __construct(
        protected ActivityLogFilter $filter,
        protected ActivityLogReportBuilder $reportBuilder,
        protected ActivityLogInsights $insights,
    ) {}

    public function index(Request $request): View
    {
        $perPage = max(1, min(100, (int) $request->query('per_page', 25)));
        $sort = TableSort::resolve($request, ['created_at', 'action'], 'created_at', 'desc');

        $query = $this->filter->apply($request);
        $filteredQuery = clone $query;
        $query->orderBy($sort['column'], $sort['direction'])->orderByDesc('id');

        $logs = $query->paginate($perPage)->appends($request->query());
        $filters = $this->normalizedFilters($request);

        return view('activity.logs.index', [
            'logs' => $logs,
            'insights' => $this->insights->forQuery($filteredQuery),
            'pageGaps' => $this->insights->gapsForPage($logs),
            'insightsService' => $this->insights,
            'filterSummary' => $this->filter->summary($filters),
            'filters' => $filters,
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

    /**
     * @return array{
     *     user: string,
     *     action: string,
     *     q: string,
     *     date_from: string,
     *     date_to: string,
     *     time_from: string,
     *     time_to: string
     * }
     */
    private function normalizedFilters(Request $request): array
    {
        return [
            'user' => (string) $request->query('user', ''),
            'action' => trim((string) $request->query('action', '')),
            'q' => trim((string) $request->query('q', '')),
            'date_from' => (string) $request->query('date_from', ''),
            'date_to' => (string) $request->query('date_to', ''),
            'time_from' => (string) $request->query('time_from', ''),
            'time_to' => (string) $request->query('time_to', ''),
        ];
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
