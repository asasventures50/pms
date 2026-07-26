<?php

namespace App\Models\Concerns;

use App\Models\Access\Permission;
use App\Models\Access\Role;
use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\PurchaseOrders\PurchaseOrder;
use App\Models\Procurement\QuickReceipts\QuickReceipt;
use App\Models\Procurement\Rfqs\Rfq;
use App\Support\Access\PermissionCatalog;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

trait HasRoles
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole(string $roleName): bool
    {
        if ($this->relationLoaded('roles')) {
            return $this->getRelation('roles')->contains('name', $roleName);
        }

        return $this->roles()->where('name', $roleName)->exists();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(PermissionCatalog::SUPER_ADMIN_ROLE);
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $granted = $this->resolvePermissionNames();

        if ($granted->contains($permission)) {
            return true;
        }

        return self::permissionImpliedByGrant($granted, $permission);
    }

    public function canViewAllProcurementRequests(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->resolvePermissionNames()->contains('procurement-requests.view');
    }

    public function scopesProcurementRequestsToOwn(): bool
    {
        if ($this->canViewAllProcurementRequests()) {
            return false;
        }

        $granted = $this->resolvePermissionNames();

        return $granted->contains('procurement-requests.view-own')
            || $granted->contains('procurement-requests.create');
    }

    public function canViewAllProcurementRequestFlows(): bool
    {
        if ($this->isSuperAdmin() || $this->canViewAllProcurementRequests()) {
            return true;
        }

        return $this->resolvePermissionNames()->contains('rfqs.view');
    }

    public function scopesProcurementRequestFlowToOwn(): bool
    {
        if ($this->canViewAllProcurementRequestFlows()) {
            return false;
        }

        $granted = $this->resolvePermissionNames();

        return $granted->contains('procurement-requests.view-own')
            || $granted->contains('procurement-requests.create');
    }

    public function canAccessProcurementRequestFlow(): bool
    {
        return $this->canViewAllProcurementRequestFlows()
            || $this->scopesProcurementRequestFlowToOwn();
    }

    public function canViewProcurementRequest(ProcurementRequest $procurementRequest): bool
    {
        if ($this->isSuperAdmin() || $this->canViewAllProcurementRequests()) {
            return true;
        }

        if ($this->resolvePermissionNames()->contains('procurement-requests.update')) {
            return true;
        }

        if ($this->scopesProcurementRequestsToOwn()) {
            return (int) $procurementRequest->created_by === (int) $this->id;
        }

        return $this->hasPermission('procurement-requests.view')
            || $this->hasPermission('procurement-requests.view-own');
    }

    public function canViewAllPurchaseOrders(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->resolvePermissionNames()->contains('purchase-orders.view');
    }

    public function scopesPurchaseOrdersToOwn(): bool
    {
        if ($this->canViewAllPurchaseOrders()) {
            return false;
        }

        $granted = $this->resolvePermissionNames();

        return $granted->contains('purchase-orders.view-own')
            || $granted->contains('purchase-orders.create');
    }

    public function canViewPurchaseOrder(PurchaseOrder $purchaseOrder): bool
    {
        if ($this->isSuperAdmin() || $this->canViewAllPurchaseOrders()) {
            return true;
        }

        if ($this->resolvePermissionNames()->contains('purchase-orders.update')) {
            return true;
        }

        if ($this->scopesPurchaseOrdersToOwn()) {
            return (int) $purchaseOrder->created_by === (int) $this->id;
        }

        return $this->hasPermission('purchase-orders.view')
            || $this->hasPermission('purchase-orders.view-own');
    }

    public function canViewAllQuickReceipts(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->resolvePermissionNames()->contains('quick-receipts.view');
    }

    public function scopesQuickReceiptsToOwn(): bool
    {
        if ($this->canViewAllQuickReceipts()) {
            return false;
        }

        $granted = $this->resolvePermissionNames();

        return $granted->contains('quick-receipts.view-own')
            || $granted->contains('quick-receipts.create');
    }

    public function canViewQuickReceipt(QuickReceipt $receipt): bool
    {
        if ($this->isSuperAdmin() || $this->canViewAllQuickReceipts()) {
            return true;
        }

        if ($this->resolvePermissionNames()->contains('quick-receipts.approve')) {
            return true;
        }

        if ($this->scopesQuickReceiptsToOwn()) {
            return (int) $receipt->user_id === (int) $this->id;
        }

        return $this->hasPermission('quick-receipts.view')
            || $this->hasPermission('quick-receipts.view-own');
    }

    public function canUpdateQuickReceipt(QuickReceipt $receipt): bool
    {
        // Approved receipts are immutable for everyone (including super-admin).
        if ($receipt->isLocked() || $receipt->isApproved()) {
            return false;
        }

        if ($this->isSuperAdmin() || $this->canViewAllQuickReceipts()) {
            return true;
        }

        if (! $this->hasPermission('quick-receipts.update') && ! $this->hasPermission('quick-receipts.create')) {
            return false;
        }

        return (int) $receipt->user_id === (int) $this->id;
    }

    public function canDeleteQuickReceipt(QuickReceipt $receipt): bool
    {
        return $this->canUpdateQuickReceipt($receipt);
    }

    public function canApproveQuickReceipts(): bool
    {
        return $this->isSuperAdmin() || $this->hasPermission('quick-receipts.approve');
    }

    public function canViewAllQuotationComparisons(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $granted = $this->resolvePermissionNames();

        return $granted->contains('rfqs.view')
            || $granted->contains('quotation-comparison.view');
    }

    public function scopesQuotationComparisonsToOwn(): bool
    {
        if ($this->canViewAllQuotationComparisons()) {
            return false;
        }

        return $this->resolvePermissionNames()->contains('quotation-comparison.view-own');
    }

    public function canViewQuotationComparison(Rfq $rfq): bool
    {
        if ($this->canViewAllQuotationComparisons()) {
            return true;
        }

        if ($this->scopesQuotationComparisonsToOwn()) {
            return $this->ownsLinkedProcurementRequestForRfq($rfq);
        }

        return $this->hasPermission('quotation-comparison.view-own')
            && $this->ownsLinkedProcurementRequestForRfq($rfq);
    }

    public function canSelectQuotationForRfq(Rfq $rfq): bool
    {
        if (! $this->hasPermission('quotation-comparison.select')) {
            return false;
        }

        if ($this->isSuperAdmin() || $this->hasPermission('rfqs.update')) {
            return true;
        }

        return $this->ownsLinkedProcurementRequestForRfq($rfq);
    }

    public function ownsLinkedProcurementRequestForRfq(Rfq $rfq): bool
    {
        if (! $rfq->relationLoaded('items')) {
            $rfq->load('items.procurementRequestItem.procurementRequest');
        }

        foreach ($rfq->items as $item) {
            $procurementRequest = $item->procurementRequestItem?->procurementRequest;
            if ($procurementRequest && (int) $procurementRequest->created_by === (int) $this->id) {
                return true;
            }
        }

        return false;
    }

    public function syncRoles(array $roleNames): void
    {
        $ids = Role::query()
            ->whereIn('name', $roleNames)
            ->pluck('id');

        $this->roles()->sync($ids);
    }

    /**
     * @return Collection<int, string>
     */
    protected function resolvePermissionNames(): Collection
    {
        if ($this->relationLoaded('roles')) {
            $roles = $this->getRelation('roles');
        } else {
            $roles = $this->roles()->with('permissions')->get();
        }

        return $roles
            ->flatMap(fn (Role $role) => $role->permissions->pluck('name'))
            ->map(fn (string $name) => PermissionCatalog::canonicalName($name))
            ->filter()
            ->unique()
            ->values();
    }

    /**
     * Higher actions imply lower ones on the same resource (update → create → view).
     *
     * @param  Collection<int, string>  $granted
     */
    private static function permissionImpliedByGrant(Collection $granted, string $required): bool
    {
        if (preg_match('/^(.+)\.view$/', $required, $viewMatches)) {
            if ($granted->contains("{$viewMatches[1]}.view-own")) {
                return true;
            }
        }

        if (! preg_match('/^(.+)\.(view|create|update)$/', $required, $matches)) {
            return false;
        }

        [, $resource, $action] = $matches;
        $levels = ['view' => 1, 'create' => 2, 'update' => 3];
        $requiredLevel = $levels[$action];

        foreach ($levels as $grantedAction => $grantedLevel) {
            if ($grantedLevel >= $requiredLevel && $granted->contains("{$resource}.{$grantedAction}")) {
                return true;
            }
        }

        return false;
    }
}
