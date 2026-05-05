@php
    $po = $purchaseOrder ?? null;
@endphp

<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="border-b border-slate-100 pb-3 text-base font-semibold text-slate-900">Purchase Order Details</h2>
    <div class="mt-4 grid gap-4 md:grid-cols-2">

        <div>
            <label for="po_number" class="block text-xs font-medium uppercase tracking-wide text-slate-500">PO Number</label>
            <input type="text" name="po_number" id="po_number"
                   value="{{ old('po_number', $po?->po_number ?? ($nextCode ?? '')) }}"
                   placeholder="Auto-generated if empty"
                   class="admin-filter-control font-mono @error('po_number') border-red-500 @enderror">
            @error('po_number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="title" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Title <span class="text-red-600">*</span></label>
            <input type="text" name="title" id="title" required
                   value="{{ old('title', $po?->title ?? '') }}"
                   class="admin-filter-control @error('title') border-red-500 @enderror">
            @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="vendor_id" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Vendor (Supplier)</label>
            <select name="vendor_id" id="vendor_id"
                    class="admin-filter-control @error('vendor_id') border-red-500 @enderror">
                <option value="">— Select vendor —</option>
                @foreach ($vendors as $vendor)
                    <option value="{{ $vendor->id }}"
                        @selected(old('vendor_id', $po?->vendor_id) == $vendor->id)>
                        {{ $vendor->vendor_code }} — {{ $vendor->name }}
                    </option>
                @endforeach
            </select>
            @error('vendor_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="total_price" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Total Price</label>
            <input type="number" name="total_price" id="total_price" min="0" step="0.01"
                   value="{{ old('total_price', $po?->total_price ?? '') }}"
                   placeholder="0.00"
                   class="admin-filter-control @error('total_price') border-red-500 @enderror">
            @error('total_price')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="ordered_at" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Order Date</label>
            <input type="date" name="ordered_at" id="ordered_at"
                   value="{{ old('ordered_at', $po?->ordered_at?->format('Y-m-d') ?? '') }}"
                   class="admin-filter-control @error('ordered_at') border-red-500 @enderror">
            @error('ordered_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="delivered_at" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Delivery Date</label>
            <input type="date" name="delivered_at" id="delivered_at"
                   value="{{ old('delivered_at', $po?->delivered_at?->format('Y-m-d') ?? '') }}"
                   class="admin-filter-control @error('delivered_at') border-red-500 @enderror">
            @error('delivered_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="status" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Order Status</label>
            <select name="status" id="status"
                    class="admin-filter-control @error('status') border-red-500 @enderror">
                @foreach (\App\Enums\Procurement\PurchaseOrders\PurchaseOrderStatus::cases() as $case)
                    <option value="{{ $case->value }}"
                        @selected(old('status', $po?->status?->value ?? 'draft') === $case->value)>
                        {{ ucfirst($case->value) }}
                    </option>
                @endforeach
            </select>
            @error('status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="payment_status" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Payment Status</label>
            <select name="payment_status" id="payment_status"
                    class="admin-filter-control @error('payment_status') border-red-500 @enderror">
                @foreach (\App\Enums\Procurement\PurchaseOrders\PaymentStatus::cases() as $case)
                    <option value="{{ $case->value }}"
                        @selected(old('payment_status', $po?->payment_status?->value ?? 'unpaid') === $case->value)>
                        {{ ucfirst($case->value) }}
                    </option>
                @endforeach
            </select>
            @error('payment_status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="md:col-span-2">
            <label for="description" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Description</label>
            <textarea name="description" id="description" rows="3"
                      class="admin-form-textarea @error('description') border-red-500 @enderror">{{ old('description', $po?->description ?? '') }}</textarea>
            @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="md:col-span-2">
            <label for="notes" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Notes</label>
            <textarea name="notes" id="notes" rows="2"
                      class="admin-form-textarea @error('notes') border-red-500 @enderror">{{ old('notes', $po?->notes ?? '') }}</textarea>
            @error('notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

    </div>
</section>
