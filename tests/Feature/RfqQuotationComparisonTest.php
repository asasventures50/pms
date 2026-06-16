<?php

namespace Tests\Feature;

use App\Models\Access\Role;
use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestItem;
use App\Models\Procurement\Rfqs\Rfq;
use App\Models\Procurement\Rfqs\RfqItem;
use App\Models\Procurement\VendorQuotations\VendorQuotation;
use App\Models\Procurement\VendorQuotations\VendorQuotationItem;
use App\Models\User;
use App\Support\Access\PermissionCatalog;
use Database\Seeders\Access\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RfqQuotationComparisonTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_pr_owner_can_view_comparison_and_select_quotation(): void
    {
        $owner = User::factory()->create();
        $owner->syncRoles([PermissionCatalog::PR_REQUESTER_ROLE]);

        $otherUser = User::factory()->create();
        $otherUser->syncRoles([PermissionCatalog::PR_REQUESTER_ROLE]);

        [$rfq, $quotationA, $quotationB] = $this->createRfqWithTwoQuotations($owner);

        $this->actingAs($owner)
            ->get(route('rfqs.comparison.show', $rfq))
            ->assertOk()
            ->assertSee('Quotation comparison')
            ->assertSee($quotationA->quotation_number)
            ->assertSee($quotationB->quotation_number);

        $this->actingAs($otherUser)
            ->get(route('rfqs.comparison.show', $rfq))
            ->assertForbidden();

        $this->actingAs($owner)
            ->post(route('rfqs.comparison.select', $rfq), [
                'vendor_quotation_id' => $quotationB->id,
            ])
            ->assertRedirect(route('rfqs.comparison.show', $rfq));

        $rfq->refresh();

        $this->assertSame($quotationB->id, $rfq->selected_vendor_quotation_id);
        $this->assertSame($owner->id, $rfq->selected_by);
        $this->assertNotNull($rfq->selected_at);
    }

    public function test_procurement_officer_can_view_and_select_any_comparison(): void
    {
        $owner = User::factory()->create();
        $officer = User::factory()->create();
        $officer->syncRoles([PermissionCatalog::PROCUREMENT_OFFICER_ROLE]);

        [$rfq, $quotationA] = $this->createRfqWithTwoQuotations($owner);

        $this->actingAs($officer)
            ->get(route('rfqs.comparison.show', $rfq))
            ->assertOk();

        $this->actingAs($officer)
            ->post(route('rfqs.comparison.select', $rfq), [
                'vendor_quotation_id' => $quotationA->id,
            ])
            ->assertRedirect(route('rfqs.comparison.show', $rfq));

        $this->assertSame($quotationA->id, $rfq->fresh()->selected_vendor_quotation_id);
    }

    public function test_pr_show_lists_related_rfqs_with_comparison_link(): void
    {
        $owner = User::factory()->create();
        $owner->syncRoles([PermissionCatalog::PR_REQUESTER_ROLE]);

        [$rfq] = $this->createRfqWithTwoQuotations($owner);
        $pr = $rfq->items->first()->procurementRequestItem->procurementRequest;

        $this->actingAs($owner)
            ->get(route('procurement-requests.show', $pr))
            ->assertOk()
            ->assertSee($rfq->rfq_number)
            ->assertSee('Compare & choose');
    }

    /**
     * @return array{0: Rfq, 1: VendorQuotation, 2: VendorQuotation}
     */
    private function createRfqWithTwoQuotations(User $prOwner): array
    {
        $procurementRequest = ProcurementRequest::query()->create([
            'request_number' => 'PR-CMP-'.uniqid(),
            'created_by' => $prOwner->id,
        ]);

        $prItem = ProcurementRequestItem::query()->create([
            'procurement_request_id' => $procurementRequest->id,
            'description' => 'Laptop computers',
            'quantity' => 10,
            'unit' => 'pcs',
        ]);

        $rfq = Rfq::query()->create([
            'rfq_number' => 'RFQ-CMP-'.uniqid(),
            'created_by' => $prOwner->id,
            'status' => 'issued',
        ]);

        $rfqItem = RfqItem::query()->create([
            'rfq_id' => $rfq->id,
            'procurement_request_item_id' => $prItem->id,
            'item' => '1',
            'description' => 'Laptop computers',
            'quantity' => 10,
            'unit' => 'pcs',
        ]);

        $quotationA = VendorQuotation::query()->create([
            'rfq_id' => $rfq->id,
            'quotation_number' => 'QUO-A-'.uniqid(),
            'vendor_company_name' => 'Vendor Alpha',
            'grand_total' => 5000,
        ]);

        VendorQuotationItem::query()->create([
            'vendor_quotation_id' => $quotationA->id,
            'rfq_item_id' => $rfqItem->id,
            'unit_price' => 500,
            'total_price' => 5000,
            'tax' => 0,
        ]);

        $quotationB = VendorQuotation::query()->create([
            'rfq_id' => $rfq->id,
            'quotation_number' => 'QUO-B-'.uniqid(),
            'vendor_company_name' => 'Vendor Beta',
            'grand_total' => 4500,
        ]);

        VendorQuotationItem::query()->create([
            'vendor_quotation_id' => $quotationB->id,
            'rfq_item_id' => $rfqItem->id,
            'unit_price' => 450,
            'total_price' => 4500,
            'tax' => 0,
        ]);

        $rfq->load('items.procurementRequestItem.procurementRequest');

        return [$rfq, $quotationA, $quotationB];
    }
}
