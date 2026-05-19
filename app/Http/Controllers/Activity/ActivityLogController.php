<?php

namespace App\Http\Controllers\Activity;

use App\Http\Controllers\Controller;
use App\Models\Activity\ActivityLog;
use App\Models\User;
use App\Support\TableSort;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = max(1, min(100, (int) $request->query('per_page', 25)));
        $sort = TableSort::resolve($request, ['created_at', 'action'], 'created_at', 'desc');

        $query = ActivityLog::query()->with('user');

        if ($request->filled('user')) {
            $query->where('user_id', (int) $request->query('user'));
        }

        if ($request->filled('action')) {
            $query->where('action', $request->string('action'));
        }

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(function ($q) use ($term) {
                $q->where('description', 'like', $term)
                    ->orWhere('action', 'like', $term)
                    ->orWhere('ip_address', 'like', $term);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->string('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->string('date_to'));
        }

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

    public function show(ActivityLog $activityLog): View
    {
        $activityLog->load('user');

        return view('activity.logs.show', [
            'log' => $activityLog,
        ]);
    }
}
