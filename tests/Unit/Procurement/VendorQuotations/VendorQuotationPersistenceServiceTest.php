<?php

namespace Tests\Unit\Procurement\VendorQuotations;

use App\Models\Procurement\Rfqs\Rfq;
use App\Models\Procurement\Rfqs\RfqItem;
use App\Services\Procurement\VendorQuotations\VendorQuotationPersistenceService;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mockery;
use Tests\TestCase;

class VendorQuotationPersistenceServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_normalize_items_calculates_total_from_unit_price_and_quantity(): void
    {
        $rfqItem = new RfqItem([
            'id' => 10,
            'quantity' => 1,
            'item' => 'PR-1-01',
        ]);

        $rfq = $this->rfqWithItems(collect([$rfqItem]));

        $items = VendorQuotationPersistenceService::normalizeItems($rfq, [
            [
                'rfq_item_id' => 10,
                'unit_price' => 10,
                'quantity_quoted' => 2,
                'total_price' => 1,
            ],
        ]);

        $this->assertCount(1, $items);
        $this->assertSame(20.0, $items[0]['total_price']);
        $this->assertSame(20.0, $items[0]['line_grand']);
    }

    public function test_normalize_items_applies_discount_before_tax(): void
    {
        $rfqItem = new RfqItem([
            'id' => 11,
            'quantity' => 1,
            'item' => 'PR-1-02',
        ]);

        $rfq = $this->rfqWithItems(collect([$rfqItem]));

        $items = VendorQuotationPersistenceService::normalizeItems($rfq, [
            [
                'rfq_item_id' => 11,
                'unit_price' => 10,
                'quantity_quoted' => 2,
                'discount' => 5,
                'tax_rate' => 10,
            ],
        ]);

        $this->assertSame(15.0, $items[0]['total_price']);
        $this->assertSame(1.5, $items[0]['tax']);
        $this->assertSame(16.5, $items[0]['line_grand']);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, RfqItem>  $items
     */
    private function rfqWithItems($items): Rfq
    {
        $relation = Mockery::mock(HasMany::class);
        $relation->shouldReceive('get')->once()->andReturn($items);

        $rfq = Mockery::mock(Rfq::class)->makePartial();
        $rfq->shouldReceive('items')->once()->andReturn($relation);

        return $rfq;
    }
}
