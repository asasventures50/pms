<?php

namespace App\Services\Activity;

use App\Models\Activity\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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

        if ($request->filled('date_from') || $request->filled('time_from')) {
            $query->where('created_at', '>=', $this->boundDateTime(
                $request->string('date_from')->toString(),
                $request->string('time_from')->toString(),
                startOfRange: true,
            ));
        }

        if ($request->filled('date_to') || $request->filled('time_to')) {
            $query->where('created_at', '<=', $this->boundDateTime(
                $request->string('date_to')->toString(),
                $request->string('time_to')->toString(),
                startOfRange: false,
            ));
        }

        return $query;
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
    public function validatedFilters(Request $request): array
    {
        $validated = $request->validate([
            'user' => ['nullable', 'integer', 'exists:users,id'],
            'action' => ['nullable', 'string', 'max:255'],
            'q' => ['nullable', 'string', 'max:255'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'time_from' => ['nullable', 'date_format:H:i'],
            'time_to' => ['nullable', 'date_format:H:i'],
        ]);

        return [
            'user' => isset($validated['user']) ? (string) $validated['user'] : '',
            'action' => trim((string) ($validated['action'] ?? '')),
            'q' => trim((string) ($validated['q'] ?? '')),
            'date_from' => (string) ($validated['date_from'] ?? ''),
            'date_to' => (string) ($validated['date_to'] ?? ''),
            'time_from' => (string) ($validated['time_from'] ?? ''),
            'time_to' => (string) ($validated['time_to'] ?? ''),
        ];
    }

    /**
     * @param  array{
     *     user: string,
     *     action: string,
     *     q: string,
     *     date_from: string,
     *     date_to: string,
     *     time_from: string,
     *     time_to: string
     * }  $filters
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

        if ($filters['date_from'] !== '' || $filters['date_to'] !== '' || $filters['time_from'] !== '' || $filters['time_to'] !== '') {
            $from = $this->formatBoundLabel(
                $filters['date_from'],
                $filters['time_from'],
                startOfRange: true,
            );
            $to = $this->formatBoundLabel(
                $filters['date_to'],
                $filters['time_to'],
                startOfRange: false,
            );
            $parts[] = "Period: {$from} to {$to}";
        }

        if ($filters['q'] !== '') {
            $parts[] = 'Search: "'.$filters['q'].'"';
        }

        return $parts !== [] ? implode(' · ', $parts) : 'All activity';
    }

    private function boundDateTime(string $date, string $time, bool $startOfRange): string
    {
        if ($date === '') {
            $date = $startOfRange ? '1970-01-01' : now()->format('Y-m-d');
        }

        if ($time === '') {
            $time = $startOfRange ? '00:00:00' : '23:59:59';
        } elseif (preg_match('/^\d{2}:\d{2}$/', $time) === 1) {
            $time = $startOfRange ? $time.':00' : $time.':59';
        }

        return Carbon::parse("{$date} {$time}")->format('Y-m-d H:i:s');
    }

    private function formatBoundLabel(string $date, string $time, bool $startOfRange): string
    {
        if ($date === '' && $time === '') {
            return '…';
        }

        if ($date === '') {
            return $time;
        }

        if ($time === '') {
            return $startOfRange ? $date.' 00:00' : $date.' 23:59';
        }

        return "{$date} {$time}";
    }
}
