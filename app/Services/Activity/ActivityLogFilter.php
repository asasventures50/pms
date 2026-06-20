<?php

namespace App\Services\Activity;

use App\Models\Activity\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ActivityLogFilter
{
    /**
     * @return Builder<ActivityLog>
     */
    public function apply(Request $request): Builder
    {
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

        return $query;
    }

    /**
     * @return array{user: string, action: string, q: string, date_from: string, date_to: string}
     */
    public function validatedFilters(Request $request): array
    {
        $validated = $request->validate([
            'user' => ['nullable', 'integer', 'exists:users,id'],
            'action' => ['nullable', 'string', 'max:255'],
            'q' => ['nullable', 'string', 'max:255'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        return [
            'user' => isset($validated['user']) ? (string) $validated['user'] : '',
            'action' => trim((string) ($validated['action'] ?? '')),
            'q' => trim((string) ($validated['q'] ?? '')),
            'date_from' => (string) ($validated['date_from'] ?? ''),
            'date_to' => (string) ($validated['date_to'] ?? ''),
        ];
    }

    /**
     * @param  array{user: string, action: string, q: string, date_from: string, date_to: string}  $filters
     */
    public function summary(array $filters): string
    {
        $parts = [];

        if ($filters['user'] !== '') {
            $user = User::query()->find((int) $filters['user']);
            $parts[] = $user ? "User: {$user->name}" : 'User filter applied';
        }

        if ($filters['action'] !== '') {
            $parts[] = 'Action: '.$filters['action'];
        }

        if ($filters['date_from'] !== '' || $filters['date_to'] !== '') {
            $from = $filters['date_from'] !== '' ? $filters['date_from'] : '…';
            $to = $filters['date_to'] !== '' ? $filters['date_to'] : '…';
            $parts[] = "Period: {$from} to {$to}";
        }

        if ($filters['q'] !== '') {
            $parts[] = 'Search: "'.$filters['q'].'"';
        }

        return $parts !== [] ? implode(' · ', $parts) : 'All activity';
    }
}
