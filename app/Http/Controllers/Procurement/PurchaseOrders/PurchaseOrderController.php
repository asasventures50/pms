<?php

namespace App\Http\Controllers\Procurement\PurchaseOrders;

use App\Enums\Procurement\PurchaseOrders\PaymentStatus;
use App\Enums\Procurement\PurchaseOrders\PurchaseOrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\PurchaseOrders\StorePurchaseOrderRequest;
use App\Http\Requests\Procurement\PurchaseOrders\UpdatePurchaseOrderRequest;
use App\Models\Procurement\PurchaseOrders\PurchaseOrder;
use App\Models\Procurement\Vendors\Vendor;
use App\Services\Procurement\PurchaseOrders\PurchaseOrderCodeGenerator;
use App\Services\Procurement\PurchaseOrders\PurchaseOrderPayloadResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = max(1, min(100, (int) $request->query('per_page', 15)));

        $query = PurchaseOrder::query()->with('vendor')->latest();

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)
                    ->orWhere('po_number', 'like', $term);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->string('payment_status'));
        }

        $purchaseOrders = $query->paginate($perPage)->withQueryString();

        return view('procurement.purchase-orders.index', [
            'purchaseOrders'  => $purchaseOrders,
            'statuses'        => PurchaseOrderStatus::cases(),
            'paymentStatuses' => PaymentStatus::cases(),
        ]);
    }

    public function create(): View
    {
        $nextCode = app(PurchaseOrderCodeGenerator::class)->next();
        $vendors  = Vendor::query()->orderBy('name')->get();

        return view('procurement.purchase-orders.create', [
            'nextCode' => $nextCode,
            'vendors'  => $vendors,
        ]);
    }

    public function store(StorePurchaseOrderRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        PurchaseOrderPayloadResolver::finalizeForStore($validated);

        $purchaseOrder = PurchaseOrder::query()->create($validated);

        return redirect()
            ->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order created successfully.');
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load('vendor');

        return view('procurement.purchase-orders.show', [
            'purchaseOrder' => $purchaseOrder,
        ]);
    }

    public function edit(PurchaseOrder $purchaseOrder): View
    {
        $vendors = Vendor::query()->orderBy('name')->get();

        return view('procurement.purchase-orders.edit', [
            'purchaseOrder' => $purchaseOrder,
            'vendors'       => $vendors,
        ]);
    }

    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $validated = $request->validated();

        PurchaseOrderPayloadResolver::finalizeForUpdate($validated);

        $purchaseOrder->fill($validated);
        $purchaseOrder->save();

        return redirect()
            ->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase order updated successfully.');
    }

    public function destroy(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $purchaseOrder->delete();

        return redirect()
            ->route('purchase-orders.index')
            ->with('success', 'Purchase order deleted successfully.');
    }
}
