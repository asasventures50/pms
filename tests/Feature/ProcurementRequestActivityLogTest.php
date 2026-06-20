<?php

namespace Tests\Feature;

use App\Models\Activity\ActivityLog;
use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcurementRequestActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_procurement_request_writes_activity_log(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $request = ProcurementRequest::query()->create([
            'request_number' => 'PR-TEST-001',
            'created_by' => $user->id,
        ]);

        $log = ActivityLog::query()->where('action', 'create_pr')->first();

        $this->assertNotNull($log);
        $this->assertSame($user->id, $log->user_id);
        $this->assertSame($request->id, $log->model_id);
        $this->assertSame('Created PR-TEST-001', $log->description);
    }
}
