<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Resolves safe column/direction sorting from query strings for use in list views.
 * Reuse across controllers by passing allowed column lists per table.
 */
final class TableSort
{
    /**
     * @param  list<string>  $allowedColumns
     * @return array{column: string, direction: string}
     */
    public static function resolve(
        Request $request,
        array $allowedColumns,
        string $defaultColumn,
        string $defaultDirection = 'asc'
    ): array {
        $sortBy = $request->query('sort_by', $defaultColumn);
        $sortDirection = strtolower((string) $request->query('sort_direction', $defaultDirection));

        if (! in_array($sortBy, $allowedColumns, true)) {
            $sortBy = $defaultColumn;
        }

        if (! in_array($sortDirection, ['asc', 'desc'], true)) {
            $sortDirection = $defaultDirection;
        }

        return [
            'column' => $sortBy,
            'direction' => $sortDirection,
        ];
    }

    /**
     * Direction to apply when the user clicks a column header (toggle if same column, else asc).
     */
    public static function nextDirection(string $currentColumn, string $clickedColumn, string $currentDirection): string
    {
        if ($currentColumn !== $clickedColumn) {
            return 'asc';
        }

        return $currentDirection === 'asc' ? 'desc' : 'asc';
    }

    /**
     * Build query params for a sort link, preserving filters and dropping page (fresh sort from page 1).
     *
     * @param  array<string, mixed>  $baseQuery
     * @return array<string, mixed>
     */
    public static function queryForColumn(
        array $baseQuery,
        string $column,
        string $currentSortColumn,
        string $currentSortDirection
    ): array {
        $params = array_merge($baseQuery, [
            'sort_by' => $column,
            'sort_direction' => self::nextDirection($currentSortColumn, $column, $currentSortDirection),
        ]);
        unset($params['page']);

        return $params;
    }
}
