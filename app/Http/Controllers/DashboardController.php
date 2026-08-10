<?php

namespace App\Http\Controllers;

use App\Enums\Procurement\ProcurementRequests\ProcurementRequestStatus;
use App\Enums\Procurement\PurchaseOrders\PaymentStatus;
use App\Models\Activity\ActivityLog;
use App\Models\Procurement\Invoices\Invoice;
use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\Projects\Project;
use App\Models\Procurement\PurchaseOrders\PurchaseOrder;
use App\Models\Procurement\Vendors\Vendor;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();

        $prStatusCounts = [
            'open' => 0,
            'in_progress' => 0,
            'closed' => 0,
        ];
        $stats = [
            'open_prs' => 0,
            'active_projects' => 0,
            'unpaid_pos' => 0,
            'vendors' => 0,
            'invoices' => 0,
        ];
        $recentActivity = collect();

        if ($isSuperAdmin) {
            $openPrStatuses = [
                ProcurementRequestStatus::Draft->value,
                ProcurementRequestStatus::Submitted->value,
                ProcurementRequestStatus::Received->value,
            ];

            if (Schema::hasTable('procurement_requests')) {
                $grouped = ProcurementRequest::query()
                    ->toBase()
                    ->selectRaw('status, COUNT(*) as aggregate')
                    ->groupBy('status')
                    ->pluck('aggregate', 'status');

                $prStatusCounts['open'] = (int) (
                    ($grouped[ProcurementRequestStatus::Draft->value] ?? 0)
                    + ($grouped[ProcurementRequestStatus::Submitted->value] ?? 0)
                );
                $prStatusCounts['in_progress'] = (int) ($grouped[ProcurementRequestStatus::Received->value] ?? 0);
                $prStatusCounts['closed'] = (int) (
                    ($grouped[ProcurementRequestStatus::Closed->value] ?? 0)
                    + ($grouped[ProcurementRequestStatus::Cancelled->value] ?? 0)
                );

                $stats['open_prs'] = ProcurementRequest::query()->whereIn('status', $openPrStatuses)->count();
            }

            $stats['active_projects'] = Schema::hasTable('projects')
                ? Project::query()->active()->count()
                : 0;

            $stats['unpaid_pos'] = Schema::hasTable('purchase_orders')
                ? PurchaseOrder::query()
                    ->whereIn('payment_status', [
                        PaymentStatus::Unpaid->value,
                        PaymentStatus::Partial->value,
                    ])
                    ->count()
                : 0;

            $stats['vendors'] = Schema::hasTable('vendors')
                ? Vendor::query()->count()
                : 0;

            $stats['invoices'] = Schema::hasTable('invoices')
                ? Invoice::query()->count()
                : 0;

            if (Schema::hasTable('activity_logs')) {
                $recentActivity = ActivityLog::query()
                    ->with('user:id,name')
                    ->latest('created_at')
                    ->limit(8)
                    ->get();
            }
        }

        return view('dashboard', [
            'isSuperAdmin' => $isSuperAdmin,
            'stats' => $stats,
            'prStatusCounts' => $prStatusCounts,
            'prStatusTotal' => array_sum($prStatusCounts),
            'recentActivity' => $recentActivity,
        ]);
    }
}
