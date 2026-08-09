<?php

namespace App\Http\Controllers\Procurement\QuickReceipts;

use App\Enums\Procurement\PrCompany;
use App\Enums\Procurement\QuickReceipts\QuickReceiptStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\QuickReceipts\SignQuickReceiptRequest;
use App\Http\Requests\Procurement\QuickReceipts\StoreQuickReceiptRequest;
use App\Http\Requests\Procurement\QuickReceipts\UpdateQuickReceiptRequest;
use App\Models\Procurement\QuickReceipts\QuickReceipt;
use App\Models\Procurement\Vendors\Category;
use App\Models\User;
use App\Services\Procurement\QuickReceipts\QuickReceiptDailyLimitService;
use App\Services\Procurement\QuickReceipts\QuickReceiptPersistenceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

class QuickReceiptController extends Controller
{
    public function __construct(
        private readonly QuickReceiptPersistenceService $persistence,
        private readonly QuickReceiptDailyLimitService $dailyLimit,
    ) {}

    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $perPage = max(1, min(100, (int) $request->query('per_page', 15)));

        $query = QuickReceipt::query()
            ->with(['user', 'approver', 'category'])
            ->latest('expense_date')
            ->latest('id');

        if ($user->scopesQuickReceiptsToOwn()) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $status = (string) $request->query('status');
            if (in_array($status, QuickReceiptStatus::values(), true)) {
                $query->where('status', $status);
            }
        }

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(function ($q) use ($term) {
                $q->where('code', 'like', $term)
                    ->orWhere('title', 'like', $term)
                    ->orWhere('provider_name', 'like', $term)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $term))
                    ->orWhereHas('category', function ($c) use ($term) {
                        $c->where('name_en', 'like', $term)
                            ->orWhere('name_ar', 'like', $term);
                    });
            });
        }

        return view('procurement.quick-receipts.index', [
            'receipts' => $query->paginate($perPage)->withQueryString(),
            'statusOptions' => QuickReceiptStatus::cases(),
            'dailyLimit' => $user->dailyReceiptLimitAmount(),
            'spentToday' => $this->dailyLimit->spentOnDate($user, now()),
            'remainingToday' => $this->dailyLimit->remainingOnDate($user, now()),
        ]);
    }

    public function create(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('procurement.quick-receipts.create', [
            'defaults' => [
                'currency_code' => $user->defaultCurrencyCode() ?? 'USD',
                'expense_date' => now()->toDateString(),
                'company_key' => PrCompany::AsasVentures->value,
            ],
            'categories' => $this->categoryOptions(),
            'companies' => PrCompany::cases(),
            'dailyLimit' => $user->dailyReceiptLimitAmount(),
            'spentToday' => $this->dailyLimit->spentOnDate($user, now()),
            'remainingToday' => $this->dailyLimit->remainingOnDate($user, now()),
        ]);
    }

    public function store(StoreQuickReceiptRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $attributes = QuickReceiptPersistenceService::attributesFromValidated($request->validated(), $user);

        /** @var UploadedFile|null $attachment */
        $attachment = $request->file('attachment');

        $receipt = $this->persistence->create($user, $attributes, $attachment);

        return redirect()
            ->route('quick-receipts.show', $receipt)
            ->with('success', 'Quick receipt submitted for approval.');
    }

    public function show(QuickReceipt $quickReceipt): View
    {
        $this->authorizeView($quickReceipt);
        $quickReceipt->load(['user', 'approver', 'category']);

        /** @var User $user */
        $user = auth()->user();

        return view('procurement.quick-receipts.show', [
            'receipt' => $quickReceipt,
            'canUpdate' => $user->canUpdateQuickReceipt($quickReceipt),
            'canDelete' => $user->canDeleteQuickReceipt($quickReceipt),
            'canApprove' => $user->canApproveQuickReceipts()
                && $quickReceipt->status === QuickReceiptStatus::PendingApproval,
            'canSign' => $user->canSignQuickReceipt($quickReceipt),
            'dailyLimit' => $quickReceipt->user?->dailyReceiptLimitAmount() ?? 200.0,
            'spentOnDate' => $this->dailyLimit->spentOnDate(
                $quickReceipt->user ?? $user,
                $quickReceipt->expense_date,
                $quickReceipt->id,
            ),
        ]);
    }

    public function edit(QuickReceipt $quickReceipt): View
    {
        $this->assertNotLocked($quickReceipt);
        $this->authorizeUpdate($quickReceipt);
        $quickReceipt->load(['user', 'category']);

        /** @var User $user */
        $user = auth()->user();

        return view('procurement.quick-receipts.edit', [
            'receipt' => $quickReceipt,
            'defaults' => [
                'title' => $quickReceipt->title,
                'description' => $quickReceipt->description,
                'amount' => $quickReceipt->amount,
                'currency_code' => $quickReceipt->displayCurrency() ?? 'USD',
                'expense_date' => $quickReceipt->expense_date?->toDateString(),
                'category_id' => $quickReceipt->category_id,
                'company_key' => $quickReceipt->company_key ?? PrCompany::AsasVentures->value,
                'provider_name' => $quickReceipt->provider_name,
            ],
            'categories' => $this->categoryOptions(),
            'companies' => PrCompany::cases(),
            'dailyLimit' => $user->dailyReceiptLimitAmount(),
            'spentToday' => $this->dailyLimit->spentOnDate($user, $quickReceipt->expense_date ?? now(), $quickReceipt->id),
            'remainingToday' => $this->dailyLimit->remainingOnDate($user, $quickReceipt->expense_date ?? now(), $quickReceipt->id),
        ]);
    }

    public function update(UpdateQuickReceiptRequest $request, QuickReceipt $quickReceipt): RedirectResponse
    {
        $this->assertNotLocked($quickReceipt);
        $this->authorizeUpdate($quickReceipt);

        /** @var User $user */
        $user = $request->user();
        $attributes = QuickReceiptPersistenceService::attributesFromValidated($request->validated(), $user);

        /** @var UploadedFile|null $attachment */
        $attachment = $request->file('attachment');

        $receipt = $this->persistence->update($quickReceipt, $attributes, $attachment);

        return redirect()
            ->route('quick-receipts.show', $receipt)
            ->with('success', 'Quick receipt updated and pending approval.');
    }

    public function destroy(QuickReceipt $quickReceipt): RedirectResponse
    {
        $this->assertNotLocked($quickReceipt);

        /** @var User|null $user */
        $user = auth()->user();
        if ($user === null || ! $user->canDeleteQuickReceipt($quickReceipt)) {
            abort(403, 'You do not have permission to delete this quick receipt.');
        }

        $code = $quickReceipt->code;
        $this->persistence->delete($quickReceipt);

        return redirect()
            ->route('quick-receipts.index')
            ->with('success', "Quick receipt {$code} deleted successfully.");
    }

    public function approve(QuickReceipt $quickReceipt): RedirectResponse
    {
        $this->authorizeApprove();

        /** @var User $user */
        $user = auth()->user();
        $receipt = $this->persistence->approve($quickReceipt, $user);

        return redirect()
            ->route('quick-receipts.show', $receipt)
            ->with('success', 'Quick receipt approved.');
    }

    public function reject(QuickReceipt $quickReceipt): RedirectResponse
    {
        $this->authorizeApprove();

        /** @var User $user */
        $user = auth()->user();
        $receipt = $this->persistence->reject($quickReceipt, $user);

        return redirect()
            ->route('quick-receipts.show', $receipt)
            ->with('success', 'Quick receipt rejected. The employee can edit and resubmit.');
    }

    public function sign(SignQuickReceiptRequest $request, QuickReceipt $quickReceipt): RedirectResponse
    {
        /** @var UploadedFile $attachment */
        $attachment = $request->file('attachment');

        $receipt = $this->persistence->sign($quickReceipt, $attachment);

        return redirect()
            ->route('quick-receipts.show', $receipt)
            ->with('success', 'Signed document uploaded. Receipt marked as signed.');
    }

    public function print(QuickReceipt $quickReceipt): View
    {
        $this->authorizeView($quickReceipt);

        if (! $quickReceipt->isPrintable()) {
            abort(403, 'Only approved or signed quick receipts can be printed.');
        }

        $quickReceipt->load(['user', 'approver', 'category']);
        $company = $quickReceipt->company();

        return view('procurement.quick-receipts.print', [
            'receipt' => $quickReceipt,
            'company' => $company,
            'buyer' => $company->details(),
            'logoUrl' => $company->logoUrl(),
            'logoExists' => $company->logoExists(),
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Category>
     */
    private function categoryOptions()
    {
        return Category::query()
            ->orderBy('name_ar')
            ->orderBy('name_en')
            ->get(['id', 'name_ar', 'name_en']);
    }

    private function authorizeView(QuickReceipt $receipt): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        if ($user === null || ! $user->canViewQuickReceipt($receipt)) {
            abort(403, 'You do not have permission to view this quick receipt.');
        }
    }

    private function assertNotLocked(QuickReceipt $receipt): void
    {
        if ($receipt->isLocked()) {
            abort(403, 'Approved or signed receipts are locked and cannot be edited or deleted.');
        }
    }

    private function authorizeUpdate(QuickReceipt $receipt): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        if ($user === null || ! $user->canUpdateQuickReceipt($receipt)) {
            abort(403, 'You do not have permission to edit this quick receipt.');
        }
    }

    private function authorizeApprove(): void
    {
        /** @var User|null $user */
        $user = auth()->user();

        if ($user === null || ! $user->canApproveQuickReceipts()) {
            abort(403, 'You do not have permission to approve quick receipts.');
        }
    }
}
