<?php

namespace App\Services\Activity;

use App\Models\Activity\ActivityLog;
use Illuminate\Support\Collection;

class ActivityLogReportBuilder
{
    /**
     * @var array<string, array{singular: string, plural: string}>
     */
    private const ENTITY_LABELS = [
        'vendor' => ['singular' => 'vendor', 'plural' => 'vendors'],
        'po' => ['singular' => 'purchase order', 'plural' => 'purchase orders'],
        'rfq' => ['singular' => 'RFQ', 'plural' => 'RFQs'],
        'vendor_quotation' => ['singular' => 'vendor quotation', 'plural' => 'vendor quotations'],
        'procurement_request' => ['singular' => 'procurement request (P.R.)', 'plural' => 'procurement requests (P.R.)'],
        'pr' => ['singular' => 'procurement request (P.R.)', 'plural' => 'procurement requests (P.R.)'],
    ];

    /**
     * @var array<string, string>
     */
    private const EVENT_VERBS = [
        'create' => 'Created',
        'update' => 'Updated',
        'delete' => 'Deleted',
        'restore' => 'Restored',
        'login' => 'Logged in',
        'logout' => 'Logged out',
    ];

    /**
     * @param  Collection<int, ActivityLog>  $logs
     * @return list<array{
     *     user_id: int|null,
     *     user_name: string,
     *     user_email: string|null,
     *     summaries: list<array{action: string, count: int, label: string}>,
     *     timeline: list<array{when: string, label: string}>
     * }>
     */
    public function build(Collection $logs): array
    {
        return $logs
            ->groupBy(fn (ActivityLog $log) => $log->user_id ?? 'system')
            ->sortKeys()
            ->map(function (Collection $userLogs, int|string $userId) {
                $first = $userLogs->first();
                $user = $first?->user;

                $summaries = $userLogs
                    ->groupBy('action')
                    ->map(fn (Collection $actionLogs, string $action) => [
                        'action' => $action,
                        'count' => $actionLogs->count(),
                        'label' => $this->summarizeAction($action, $actionLogs->count()),
                    ])
                    ->sortBy('label')
                    ->values()
                    ->all();

                $timeline = $userLogs
                    ->sortBy([
                        ['created_at', 'asc'],
                        ['id', 'asc'],
                    ])
                    ->map(fn (ActivityLog $log) => [
                        'when' => $log->created_at?->format('Y-m-d H:i') ?? '—',
                        'label' => $log->description ?? $this->summarizeAction($log->action, 1),
                    ])
                    ->values()
                    ->all();

                return [
                    'user_id' => is_numeric($userId) ? (int) $userId : null,
                    'user_name' => $user?->name ?? 'System / unknown user',
                    'user_email' => $user?->email,
                    'summaries' => $summaries,
                    'timeline' => $timeline,
                ];
            })
            ->values()
            ->all();
    }

    public function summarizeAction(string $action, int $count): string
    {
        $count = max(1, $count);
        $parsed = $this->parseAction($action);

        if ($parsed['entity'] === null) {
            $verb = self::EVENT_VERBS[$parsed['event']] ?? ucfirst(str_replace('_', ' ', $parsed['event']));

            return $count === 1
                ? $verb
                : "{$verb} {$count} times";
        }

        $labels = self::ENTITY_LABELS[$parsed['entity']] ?? [
            'singular' => str_replace('_', ' ', $parsed['entity']),
            'plural' => str_replace('_', ' ', $parsed['entity']).'s',
        ];

        $verb = self::EVENT_VERBS[$parsed['event']] ?? ucfirst($parsed['event']);
        $entity = $count === 1 ? $labels['singular'] : $labels['plural'];

        if ($count === 1) {
            return "{$verb} {$entity}";
        }

        return "{$verb} {$count} {$entity}";
    }

    /**
     * @return array{event: string, entity: string|null}
     */
    private function parseAction(string $action): array
    {
        if (preg_match('/^(create|update|delete|restore)_(.+)$/', $action, $matches) === 1) {
            return [
                'event' => $matches[1],
                'entity' => $matches[2],
            ];
        }

        return [
            'event' => $action,
            'entity' => null,
        ];
    }
}
