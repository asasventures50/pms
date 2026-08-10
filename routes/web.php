<?php

/**
 * Web (session) routes. Keep JSON/token APIs in `routes/api.php` (e.g. with Laravel Sanctum later).
 */

use App\Http\Controllers\Access\RoleController;
use App\Http\Controllers\Access\UserController;
use App\Http\Controllers\Activity\ActivityLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Geo\CityController;
use App\Http\Controllers\Geo\CountryController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Procurement\Catalog\CategoryQuickStoreController;
use App\Http\Controllers\Procurement\Catalog\SubcategoryQuickStoreController;
use App\Http\Controllers\Procurement\Categories\CategoryController;
use App\Http\Controllers\Procurement\Invoices\InvoiceController;
use App\Http\Controllers\Procurement\ProcurementRequests\ProcurementRequestController;
use App\Http\Controllers\Procurement\ProcurementRequests\ProcurementRequestFlowController;
use App\Http\Controllers\Procurement\Projects\ProjectController;
use App\Http\Controllers\Procurement\Projects\ProjectQuickStoreController;
use App\Http\Controllers\Procurement\Projects\ZoneQuickStoreController;
use App\Http\Controllers\Procurement\PurchaseOrders\PurchaseOrderController;
use App\Http\Controllers\Procurement\QuickReceipts\QuickReceiptController;
use App\Http\Controllers\Procurement\Rfqs\PublicVendorQuotationController;
use App\Http\Controllers\Procurement\Rfqs\RfqController;
use App\Http\Controllers\Procurement\Rfqs\RfqGeneralTermController;
use App\Http\Controllers\Procurement\Rfqs\RfqQuotationComparisonController;
use App\Http\Controllers\Procurement\Rfqs\RfqVendorQuotationInviteController;
use App\Http\Controllers\Procurement\ScheduleOfWorks\ScheduleOfWorkController;
use App\Http\Controllers\Procurement\VendorQuotations\VendorQuotationController;
use App\Http\Controllers\Procurement\Vendors\VendorRegistrationController;
use App\Http\Controllers\Procurement\Vendors\VendorWebController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('landing');

Route::middleware('public-form-locale')->group(function () {
    Route::get('vendor-registration', [VendorRegistrationController::class, 'create'])
        ->name('vendor-registration.create');

    Route::post('vendor-registration', [VendorRegistrationController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('vendor-registration.store');

    Route::get('vendor-registration/thanks', [VendorRegistrationController::class, 'thanks'])
        ->name('vendor-registration.thanks');

    Route::get('vendor-quotation/{invite:token}', [PublicVendorQuotationController::class, 'show'])
        ->name('vendor-quotation-invite.show');

    Route::get('vendor-quotation/{invite:token}/excel-template', [PublicVendorQuotationController::class, 'downloadExcel'])
        ->middleware('throttle:20,1')
        ->name('vendor-quotation-invite.excel-template');

    Route::post('vendor-quotation/{invite:token}', [PublicVendorQuotationController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('vendor-quotation-invite.store');

    Route::post('vendor-quotation/{invite:token}/excel', [PublicVendorQuotationController::class, 'storeExcel'])
        ->middleware('throttle:10,1')
        ->name('vendor-quotation-invite.excel-store');
});

require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('users', UserController::class)
        ->except(['show'])
        ->middlewareFor('index', 'permission:users.view')
        ->middlewareFor(['create', 'store'], 'permission:users.create')
        ->middlewareFor(['edit', 'update'], 'permission:users.update')
        ->middlewareFor('destroy', 'permission:users.delete');

    Route::resource('roles', RoleController::class)
        ->except(['show'])
        ->middlewareFor('index', 'permission:roles.view')
        ->middlewareFor(['create', 'store'], 'permission:roles.create')
        ->middlewareFor(['edit', 'update'], 'permission:roles.update')
        ->middlewareFor('destroy', 'permission:roles.delete');

    Route::get('activity-logs', [ActivityLogController::class, 'index'])
        ->middleware('permission:activity-logs.view')
        ->name('activity-logs.index');

    Route::get('activity-logs/report', [ActivityLogController::class, 'report'])
        ->middleware('permission:activity-logs.view')
        ->name('activity-logs.report');

    Route::get('activity-logs/{activityLog}', [ActivityLogController::class, 'show'])
        ->middleware('permission:activity-logs.view')
        ->name('activity-logs.show');

    Route::get('/categories/export', [CategoryController::class, 'export'])
        ->middleware('permission:categories.export')
        ->name('categories.export');

    Route::get('/categories/import/template', [CategoryController::class, 'downloadTemplate'])
        ->middleware('permission:categories.import')
        ->name('categories.import.template');

    Route::get('/categories/import', [CategoryController::class, 'importForm'])
        ->middleware('permission:categories.import')
        ->name('categories.import.form');

    Route::post('/categories/import', [CategoryController::class, 'import'])
        ->middleware('permission:categories.import')
        ->name('categories.import');

    Route::get('/categories/subcategories/{subcategory}/move-preview', [CategoryController::class, 'movePreview'])
        ->middleware('permission:categories.update')
        ->name('categories.subcategories.move-preview');

    Route::get('/categories/{category}/vendor-links', [CategoryController::class, 'categoryVendorLinks'])
        ->middleware('permission:categories.view')
        ->name('categories.vendor-links');

    Route::get('/categories/{category}/subcategories/{subcategory}/vendor-links', [CategoryController::class, 'subcategoryVendorLinks'])
        ->middleware('permission:categories.view')
        ->name('categories.subcategories.vendor-links');

    Route::put('/vendor-categories/{vendorCategory}/reassign', [CategoryController::class, 'reassignVendorLink'])
        ->middleware('permission:categories.update')
        ->name('vendor-categories.reassign');

    Route::delete('/vendor-categories/{vendorCategory}', [CategoryController::class, 'removeVendorLink'])
        ->middleware('permission:categories.update')
        ->name('vendor-categories.destroy');

    Route::resource('categories', CategoryController::class)
        ->middlewareFor(['index', 'show'], 'permission:categories.view')
        ->middlewareFor(['create', 'store'], 'permission:categories.create')
        ->middlewareFor(['edit', 'update', 'destroy'], 'permission:categories.update');

    Route::post('/categories/quick-store', [CategoryQuickStoreController::class, 'quickStore'])
        ->middleware('permission:categories.create')
        ->name('categories.quick-store');

    Route::post('/subcategories/quick-store', [SubcategoryQuickStoreController::class, 'quickStore'])
        ->middleware('permission:categories.create|procurement-requests.create')
        ->name('subcategories.quick-store');

    Route::post('/projects/quick-store', [ProjectQuickStoreController::class, 'quickStore'])
        ->middleware('permission:projects.create')
        ->name('projects.quick-store');

    Route::post('/zones/quick-store', [ZoneQuickStoreController::class, 'quickStore'])
        ->middleware('permission:projects.update')
        ->name('zones.quick-store');

    Route::resource('projects', ProjectController::class)
        ->middlewareFor(['index', 'show'], 'permission:projects.view')
        ->middlewareFor(['create', 'store'], 'permission:projects.create')
        ->middlewareFor(['edit', 'update', 'destroy'], 'permission:projects.update');

    Route::get('/locations', [CountryController::class, 'index'])
        ->middleware('permission:locations.view')
        ->name('locations.index');

    Route::resource('countries', CountryController::class)
        ->except(['show'])
        ->middleware('permission:locations.manage');

    Route::resource('cities', CityController::class)
        ->except(['show'])
        ->middleware('permission:locations.manage');

    Route::get('/vendors/search-for-select', [VendorWebController::class, 'searchForSelect'])
        ->middleware('permission:purchase-orders.create|purchase-orders.update|rfqs.create|rfqs.update')
        ->name('vendors.search-for-select');

    Route::get('/vendors/{vendor}/purchase-order-snapshot', [PurchaseOrderController::class, 'vendorSnapshot'])
        ->middleware('permission:purchase-orders.create|purchase-orders.update')
        ->name('vendors.purchase-order-snapshot');

    Route::get('/procurement-requests/{procurementRequest}/purchase-order-lines', [PurchaseOrderController::class, 'procurementRequestLines'])
        ->middleware('permission:purchase-orders.create|purchase-orders.update')
        ->name('procurement-requests.purchase-order-lines');

    Route::get('/vendors/{vendor}/rfq-snapshot', [RfqController::class, 'vendorSnapshot'])
        ->middleware('permission:rfqs.create|rfqs.update|vendor-quotations.create|vendor-quotations.update')
        ->name('vendors.rfq-snapshot');

    Route::get('/vendors/export', [VendorWebController::class, 'export'])
        ->middleware('permission:vendors.view')
        ->name('vendors.export');

    Route::get('/vendors/import', [VendorWebController::class, 'importForm'])
        ->middleware('permission:vendors.create')
        ->name('vendors.import.form');

    Route::post('/vendors/import', [VendorWebController::class, 'import'])
        ->middleware('permission:vendors.create')
        ->name('vendors.import');

    Route::resource('vendors', VendorWebController::class)
        ->middlewareFor(['index', 'show'], 'permission:vendors.view')
        ->middlewareFor(['create', 'store'], 'permission:vendors.create')
        ->middlewareFor(['edit', 'update'], 'permission:vendors.update')
        ->middlewareFor('destroy', 'permission:vendors.delete');

    Route::get('purchase-orders/{purchase_order}/print', [PurchaseOrderController::class, 'print'])
        ->middleware('permission:purchase-orders.view|purchase-orders.view-own')
        ->name('purchase-orders.print');

    Route::resource('purchase-orders', PurchaseOrderController::class)
        ->middlewareFor(['index', 'show'], 'permission:purchase-orders.view|purchase-orders.view-own')
        ->middlewareFor(['create', 'store'], 'permission:purchase-orders.create')
        ->middlewareFor(['edit', 'update', 'destroy'], 'permission:purchase-orders.update');

    Route::get('purchase-orders/{purchase_order}/invoice-items', [InvoiceController::class, 'purchaseOrderItems'])
        ->middleware('permission:invoices.create')
        ->name('purchase-orders.invoice-items');

    Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])
        ->middleware('permission:invoices.create')
        ->name('invoices.print');

    Route::get('invoices/{invoice}/export', [InvoiceController::class, 'export'])
        ->middleware('permission:invoices.create')
        ->name('invoices.export');

    Route::resource('invoices', InvoiceController::class)
        ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'])
        ->middleware('permission:invoices.create');

    Route::get('quick-receipts/{quick_receipt}/print', [QuickReceiptController::class, 'print'])
        ->middleware('permission:quick-receipts.view|quick-receipts.view-own|quick-receipts.approve')
        ->name('quick-receipts.print');

    Route::post('quick-receipts/{quick_receipt}/approve', [QuickReceiptController::class, 'approve'])
        ->middleware('permission:quick-receipts.approve')
        ->name('quick-receipts.approve');

    Route::post('quick-receipts/{quick_receipt}/reject', [QuickReceiptController::class, 'reject'])
        ->middleware('permission:quick-receipts.approve')
        ->name('quick-receipts.reject');

    Route::post('quick-receipts/{quick_receipt}/sign', [QuickReceiptController::class, 'sign'])
        ->middleware('permission:quick-receipts.create|quick-receipts.update')
        ->name('quick-receipts.sign');

    Route::resource('quick-receipts', QuickReceiptController::class)
        ->middlewareFor(['index', 'show'], 'permission:quick-receipts.view|quick-receipts.view-own|quick-receipts.approve')
        ->middlewareFor(['create', 'store'], 'permission:quick-receipts.create')
        ->middlewareFor(['edit', 'update', 'destroy'], 'permission:quick-receipts.update|quick-receipts.create');

    Route::get('procurement-requests/{procurement_request}/schedule-of-work-items', [ScheduleOfWorkController::class, 'procurementRequestItems'])
        ->middleware('permission:schedule-of-works.create')
        ->name('procurement-requests.schedule-of-work-items');

    Route::get('schedule-of-works/{schedule_of_work}/print', [ScheduleOfWorkController::class, 'print'])
        ->middleware('permission:schedule-of-works.create')
        ->name('schedule-of-works.print');

    Route::resource('schedule-of-works', ScheduleOfWorkController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
        ->parameters(['schedule-of-works' => 'schedule_of_work'])
        ->middleware('permission:schedule-of-works.create');

    Route::get('rfq-terms/print', [RfqGeneralTermController::class, 'print'])
        ->middleware('permission:rfq-terms.view')
        ->name('rfq-terms.print');

    Route::resource('rfq-terms', RfqGeneralTermController::class)
        ->except(['show'])
        ->parameters(['rfq-terms' => 'rfq_term'])
        ->middlewareFor('index', 'permission:rfq-terms.view')
        ->middlewareFor(['create', 'store', 'edit', 'update', 'destroy'], 'permission:rfq-terms.manage');

    Route::resource('rfqs', RfqController::class)
        ->middlewareFor(['index', 'show'], 'permission:rfqs.view')
        ->middlewareFor(['create', 'store'], 'permission:rfqs.create')
        ->middlewareFor(['edit', 'update', 'destroy'], 'permission:rfqs.update');

    Route::post('rfqs/{rfq}/vendor-quotation-invites', [RfqVendorQuotationInviteController::class, 'store'])
        ->middleware('permission:vendor-quotations.create|rfqs.update')
        ->name('rfqs.vendor-quotation-invites.store');

    Route::get('rfqs/{rfq}/comparison', [RfqQuotationComparisonController::class, 'show'])
        ->name('rfqs.comparison.show');

    Route::post('rfqs/{rfq}/comparison/select', [RfqQuotationComparisonController::class, 'select'])
        ->name('rfqs.comparison.select');

    Route::post('rfqs/{rfq}/comparison/clear-selection', [RfqQuotationComparisonController::class, 'clearSelection'])
        ->name('rfqs.comparison.clear-selection');

    Route::get('rfqs/{rfq}/quotations/{quotation}/print', [VendorQuotationController::class, 'print'])
        ->middleware('permission:vendor-quotations.view|rfqs.view')
        ->name('rfqs.quotations.print');

    Route::resource('rfqs.quotations', VendorQuotationController::class)
        ->parameters(['quotations' => 'quotation'])
        ->except(['index'])
        ->middlewareFor(['create', 'store'], 'permission:vendor-quotations.create|rfqs.update')
        ->middlewareFor('show', 'permission:vendor-quotations.view|rfqs.view')
        ->middlewareFor(['edit', 'update', 'destroy'], 'permission:vendor-quotations.update|rfqs.update');

    Route::get('procurement-requests/{procurement_request}/print', [ProcurementRequestController::class, 'print'])
        ->middleware('permission:procurement-requests.view|procurement-requests.view-own')
        ->name('procurement-requests.print');

    Route::get('my-procurement-requests/flow', [ProcurementRequestFlowController::class, 'index'])
        ->middleware('permission:procurement-requests.view|procurement-requests.view-own|procurement-requests.create|rfqs.view')
        ->name('procurement-requests.my-flow');

    Route::resource('procurement-requests', ProcurementRequestController::class)
        ->middlewareFor(['index', 'show'], 'permission:procurement-requests.view|procurement-requests.view-own')
        ->middlewareFor(['create', 'store'], 'permission:procurement-requests.create')
        ->middlewareFor(['edit', 'update', 'destroy'], 'permission:procurement-requests.update');
});
