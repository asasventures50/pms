@php
    use App\Enums\Procurement\QuickReceipts\QuickReceiptStatus;

    /** @var QuickReceiptStatus|null $status */
    $status = $status ?? null;
@endphp

@if ($status)
    <span @class([
        'inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold',
        'bg-slate-100 text-slate-700' => $status === QuickReceiptStatus::Draft,
        'bg-amber-100 text-amber-900' => $status === QuickReceiptStatus::PendingApproval,
        'bg-emerald-100 text-emerald-800' => $status === QuickReceiptStatus::Approved,
        'bg-red-100 text-red-800' => $status === QuickReceiptStatus::Rejected,
    ])>
        {{ $status->label() }}
    </span>
@endif
