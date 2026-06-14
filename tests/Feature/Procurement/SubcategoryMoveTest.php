<?php

namespace Tests\Feature\Procurement;

use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\Vendors\Category;
use App\Models\Procurement\Vendors\Subcategory;
use App\Models\Procurement\Vendors\Vendor;
use App\Models\Procurement\Vendors\VendorBrochure;
use App\Models\Procurement\Vendors\VendorCategory;
use App\Models\User;
use App\Services\Procurement\Categories\SubcategoryMoveService;
use Database\Seeders\Access\RolePermissionSeeder;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class SubcategoryMoveTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(AdminUserSeeder::class);

        $this->admin = User::query()->where('email', 'pms@tamkeensy.com')->firstOrFail();
    }

    public function test_move_subcategory_updates_vendor_category_links(): void
    {
        [$sourceCategory, $targetCategory, $subcategory, $vendor] = $this->seedMoveScenario();

        $result = app(SubcategoryMoveService::class)->move($subcategory, $targetCategory);

        $this->assertSame(1, $result->vendorLinksUpdated);
        $this->assertDatabaseHas('vendor_categories', [
            'vendor_id' => $vendor->id,
            'category_id' => $targetCategory->id,
            'subcategory_id' => $subcategory->id,
        ]);
        $this->assertDatabaseMissing('vendor_categories', [
            'vendor_id' => $vendor->id,
            'category_id' => $sourceCategory->id,
            'subcategory_id' => $subcategory->id,
        ]);
        $this->assertSame($targetCategory->id, $subcategory->fresh()->category_id);
    }

    public function test_move_subcategory_updates_brochures_and_procurement_requests(): void
    {
        [$sourceCategory, $targetCategory, $subcategory] = $this->seedMoveScenario();

        $vendor = Vendor::query()->firstOrFail();

        $brochure = VendorBrochure::query()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $sourceCategory->id,
            'subcategory_id' => $subcategory->id,
            'file_name' => 'brochure.pdf',
            'file_path' => 'vendors/brochure.pdf',
        ]);

        $procurementRequest = ProcurementRequest::query()->create([
            'request_number' => 'PR-MOVE-001',
            'category_id' => $sourceCategory->id,
            'subcategory_id' => $subcategory->id,
        ]);

        $result = app(SubcategoryMoveService::class)->move($subcategory, $targetCategory);

        $this->assertSame(1, $result->brochuresUpdated);
        $this->assertSame(1, $result->procurementRequestsUpdated);
        $this->assertSame($targetCategory->id, $brochure->fresh()->category_id);
        $this->assertSame($targetCategory->id, $procurementRequest->fresh()->category_id);
    }

    public function test_category_update_moves_subcategory_from_form(): void
    {
        [$sourceCategory, $targetCategory, $subcategory, $vendor] = $this->seedMoveScenario();

        $response = $this->actingAs($this->admin)->put(route('categories.update', $sourceCategory), [
            'name_en' => $sourceCategory->name_en,
            'name_ar' => $sourceCategory->name_ar,
            'slug' => $sourceCategory->slug,
            'status' => $sourceCategory->status,
            'subcategories' => [
                [
                    'id' => $subcategory->id,
                    'name_en' => $subcategory->name_en,
                    'name_ar' => $subcategory->name_ar,
                    'slug' => $subcategory->slug,
                    'status' => $subcategory->status,
                    'target_category_id' => $targetCategory->id,
                ],
            ],
        ]);

        $response->assertRedirect(route('categories.show', $sourceCategory));
        $response->assertSessionHas('success');

        $this->assertSame($targetCategory->id, $subcategory->fresh()->category_id);
        $this->assertDatabaseHas('vendor_categories', [
            'vendor_id' => $vendor->id,
            'category_id' => $targetCategory->id,
            'subcategory_id' => $subcategory->id,
        ]);
        $this->assertStringContainsString('Warning:', session('success'));
    }

    public function test_move_rejected_when_slug_conflicts_in_target_category(): void
    {
        [$sourceCategory, $targetCategory, $subcategory] = $this->seedMoveScenario();

        Subcategory::query()->create([
            'category_id' => $targetCategory->id,
            'name_en' => 'Existing Sub',
            'name_ar' => 'Existing Sub AR',
            'slug' => $subcategory->slug,
            'status' => 'active',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('slug is already used in the target category');

        app(SubcategoryMoveService::class)->move($subcategory, $targetCategory);
    }

    public function test_category_update_rejects_move_when_slug_conflicts_in_target_category(): void
    {
        [$sourceCategory, $targetCategory, $subcategory] = $this->seedMoveScenario();

        Subcategory::query()->create([
            'category_id' => $targetCategory->id,
            'name_en' => 'Existing Sub',
            'name_ar' => 'Existing Sub AR',
            'slug' => $subcategory->slug,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->from(route('categories.edit', $sourceCategory))
            ->put(route('categories.update', $sourceCategory), [
                'name_en' => $sourceCategory->name_en,
                'name_ar' => $sourceCategory->name_ar,
                'slug' => $sourceCategory->slug,
                'status' => $sourceCategory->status,
                'subcategories' => [
                    [
                        'id' => $subcategory->id,
                        'name_en' => $subcategory->name_en,
                        'name_ar' => $subcategory->name_ar,
                        'slug' => $subcategory->slug,
                        'status' => $subcategory->status,
                        'target_category_id' => $targetCategory->id,
                    ],
                ],
            ]);

        $response->assertRedirect(route('categories.edit', $sourceCategory));
        $response->assertSessionHasErrors('subcategories.0.slug');
        $this->assertSame($sourceCategory->id, $subcategory->fresh()->category_id);
    }

    public function test_move_preview_returns_impact_counts(): void
    {
        [$sourceCategory, $targetCategory, $subcategory] = $this->seedMoveScenario();

        ProcurementRequest::query()->create([
            'request_number' => 'PR-PREVIEW-001',
            'category_id' => $sourceCategory->id,
            'subcategory_id' => $subcategory->id,
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('categories.subcategories.move-preview', [
            'subcategory' => $subcategory,
            'target_category_id' => $targetCategory->id,
        ]));

        $response->assertOk();
        $response->assertJson([
            'vendor_links' => 1,
            'brochures' => 0,
            'procurement_requests' => 1,
            'has_name_conflict' => false,
            'has_slug_conflict' => false,
        ]);
    }

    public function test_subcategory_is_listed_under_new_parent_after_move(): void
    {
        [$sourceCategory, $targetCategory, $subcategory] = $this->seedMoveScenario();

        app(SubcategoryMoveService::class)->move($subcategory, $targetCategory);

        $this->assertFalse($sourceCategory->subcategories()->whereKey($subcategory->id)->exists());
        $this->assertTrue($targetCategory->subcategories()->whereKey($subcategory->id)->exists());
    }

    /**
     * @return array{0: Category, 1: Category, 2: Subcategory, 3: Vendor}
     */
    private function seedMoveScenario(): array
    {
        $sourceCategory = Category::query()->create([
            'name_en' => 'Source Category',
            'name_ar' => 'تصنيف مصدر',
            'slug' => 'source-category',
            'status' => 'active',
        ]);

        $targetCategory = Category::query()->create([
            'name_en' => 'Target Category',
            'name_ar' => 'تصنيف هدف',
            'slug' => 'target-category',
            'status' => 'active',
        ]);

        $subcategory = Subcategory::query()->create([
            'category_id' => $sourceCategory->id,
            'name_en' => 'Movable Sub',
            'name_ar' => 'فرعي',
            'slug' => 'movable-sub',
            'status' => 'active',
        ]);

        $vendor = Vendor::query()->create([
            'vendor_code' => 'V-MOVE-001',
            'name' => 'Move Test Vendor',
            'language' => 'en',
            'status' => 'active',
        ]);

        VendorCategory::query()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $sourceCategory->id,
            'subcategory_id' => $subcategory->id,
            'is_primary' => true,
        ]);

        return [$sourceCategory, $targetCategory, $subcategory, $vendor];
    }
}
