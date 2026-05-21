<?php

namespace Database\Seeders\Procurement;

use App\Models\Procurement\Projects\Project;
use App\Models\Procurement\Projects\Zone;
use Illuminate\Database\Seeder;

class ProjectZoneSeeder extends Seeder
{
    public function run(): void
    {
        /** @var list<array{code: string, name: string, zones: list<array{code: string, name: string}>}> $catalog */
        $catalog = [
            [
                'code' => 'PRJ-001',
                'name' => 'Main Campus',
                'zones' => [
                    ['code' => 'Z-A', 'name' => 'Zone A — North'],
                    ['code' => 'Z-B', 'name' => 'Zone B — South'],
                ],
            ],
            [
                'code' => 'PRJ-002',
                'name' => 'Warehouse Expansion',
                'zones' => [
                    ['code' => 'Z-01', 'name' => 'Storage'],
                    ['code' => 'Z-02', 'name' => 'Loading'],
                ],
            ],
        ];

        foreach ($catalog as $projectData) {
            $zones = $projectData['zones'];
            unset($projectData['zones']);

            $project = Project::query()->updateOrCreate(
                ['code' => $projectData['code']],
                ['name' => $projectData['name'], 'status' => 'active']
            );

            foreach ($zones as $zoneData) {
                Zone::query()->updateOrCreate(
                    [
                        'project_id' => $project->id,
                        'code' => $zoneData['code'],
                    ],
                    ['name' => $zoneData['name'], 'status' => 'active']
                );
            }
        }
    }
}
