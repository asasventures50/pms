<?php

namespace App\Services\Procurement\Projects;

use App\Models\Procurement\Projects\Project;
use App\Models\Procurement\Projects\Zone;
use Illuminate\Support\Facades\DB;

class ProjectCatalogService
{
    public function __construct(
        private readonly ZoneCodeGenerator $zoneCodes,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function syncZones(Project $project, array $rows): void
    {
        $normalized = $this->normalizeZoneRows($rows);
        $keptIds = [];

        foreach ($normalized as $row) {
            if (! empty($row['id'])) {
                $zone = Zone::query()
                    ->where('project_id', $project->id)
                    ->whereKey($row['id'])
                    ->first();

                if ($zone) {
                    $zone->fill([
                        'name' => $row['name'],
                        'status' => $row['status'],
                    ]);
                    $zone->save();
                    $keptIds[] = $zone->id;

                    continue;
                }
            }

            $created = Zone::query()->create([
                'project_id' => $project->id,
                'code' => $this->zoneCodes->nextForProject($project->id),
                'name' => $row['name'],
                'status' => $row['status'],
            ]);
            $keptIds[] = $created->id;
        }

        Zone::query()
            ->where('project_id', $project->id)
            ->whereNotIn('id', $keptIds)
            ->get()
            ->each(fn (Zone $zone) => $zone->delete());
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return list<array{id?: int, name: string, status: string}>
     */
    public function normalizeZoneRows(array $rows): array
    {
        $out = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $name = isset($row['name']) ? trim((string) $row['name']) : '';
            $status = isset($row['status']) ? trim((string) $row['status']) : '';

            if ($name === '') {
                continue;
            }

            $out[] = [
                'id' => isset($row['id']) && $row['id'] !== '' ? (int) $row['id'] : null,
                'name' => $name,
                'status' => $status !== '' ? $status : 'active',
            ];
        }

        return $out;
    }

    public function softDeleteProjectCascade(Project $project): void
    {
        DB::transaction(function () use ($project) {
            Zone::query()
                ->where('project_id', $project->id)
                ->get()
                ->each(fn (Zone $zone) => $zone->delete());

            $project->delete();
        });
    }
}
