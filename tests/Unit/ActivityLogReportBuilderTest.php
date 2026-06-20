<?php

namespace Tests\Unit;

use App\Models\Activity\ActivityLog;
use App\Models\User;
use App\Services\Activity\ActivityLogReportBuilder;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ActivityLogReportBuilderTest extends TestCase
{
    private ActivityLogReportBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = new ActivityLogReportBuilder;
    }

    #[DataProvider('actionSummaryProvider')]
    public function test_summarize_action(string $action, int $count, string $expected): void
    {
        $this->assertSame($expected, $this->builder->summarizeAction($action, $count));
    }

    /**
     * @return list<array{string, int, string}>
     */
    public static function actionSummaryProvider(): array
    {
        return [
            ['create_vendor', 1, 'Created vendor'],
            ['create_vendor', 8, 'Created 8 vendors'],
            ['update_po', 2, 'Updated 2 purchase orders'],
            ['login', 1, 'Logged in'],
            ['login', 3, 'Logged in 3 times'],
            ['create_pr', 1, 'Created procurement request (P.R.)'],
        ];
    }

    public function test_build_groups_logs_by_user_with_summary_and_timeline(): void
    {
        $logs = collect([
            $this->makeLog(1, 10, 'create_vendor', 'Created V-001'),
            $this->makeLog(2, 10, 'create_vendor', 'Created V-002'),
            $this->makeLog(3, 10, 'create_po', 'Created PO-100'),
            $this->makeLog(4, 20, 'login', 'User logged in'),
        ]);

        $report = $this->builder->build($logs);

        $this->assertCount(2, $report);

        $fadi = $report[0];
        $this->assertSame('Fadi', $fadi['user_name']);
        $this->assertSame([
            ['action' => 'create_vendor', 'count' => 2, 'label' => 'Created 2 vendors'],
            ['action' => 'create_po', 'count' => 1, 'label' => 'Created purchase order'],
        ], $fadi['summaries']);
        $this->assertCount(3, $fadi['timeline']);

        $sara = $report[1];
        $this->assertSame('Sara', $sara['user_name']);
        $this->assertSame('Logged in', $sara['summaries'][0]['label']);
    }

    private function makeLog(int $id, int $userId, string $action, string $description): ActivityLog
    {
        $log = new ActivityLog([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'created_at' => now()->addMinutes($id),
        ]);
        $log->id = $id;

        $user = new User([
            'id' => $userId,
            'name' => $userId === 10 ? 'Fadi' : 'Sara',
            'email' => $userId === 10 ? 'fadi@example.com' : 'sara@example.com',
        ]);

        $log->setRelation('user', $user);

        return $log;
    }
}
