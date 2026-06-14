<?php

namespace Tests\Feature\Procurement;

use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\Vendors\Category;
use App\Models\Procurement\Vendors\Subcategory;
use App\Models\Procurement\Vendors\Vendor;
use App\Models\Procurement\Vendors\VendorBrochure;
use App\Models\Procurement\Vendors\VendorCategory;
use App\Models\User;
use App\Services\Procurement\Categories\CategoryVendorLinkService;
use Database\Seeders\Access\RolePermissionSeeder;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryVendorLinkTest extends TestCase
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

    public function test_subcategory_vendor_links_page_lists_vendors(): void
    {
        [$sourceCategory, $targetCategory, $subcategory, $vendor, $link] = $this->seedLinkScenario();

        $response = $this->actingAs($this->admin)->get(route('categories.subcategories.vendor-links', [
            'category' => $sourceCategory,
            'subcategory' => $subcategory,
        ]));

        $response->assertOk();
        $response->assertSee($vendor->name);
        $response->assertSee('Reassign vendor');
    }

    public function test_reassign_vendor_link_updates_classification_without_touching_procurement_requests(): void
    {
        [$sourceCategory, $targetCategory, $subcategory, $vendor, $link] = $this->seedLinkScenario();

        $targetSub = Subcategory::query()->create([
            'category_id' => $targetCategory->id,
            'name_en' => 'Target Sub',
            'name_ar' => 'فرعي هدف',
            'slug' => 'target-sub-link',
            'status' => 'active',
        ]);

        $procurementRequest = ProcurementRequest::query()->create([
            'request_number' => 'PR-LINK-001',
            'category_id' => $sourceCategory->id,
            'subcategory_id' => $subcategory->id,
        ]);

        $response = $this->actingAs($this->admin)->put(route('vendor-categories.reassign', $link), [
            'target_category_id' => $targetCategory->id,
            'target_subcategory_id' => $targetSub->id,
            'return_url' => route('categories.subcategories.vendor-links', [
                'category' => $sourceCategory,
                'subcategory' => $subcategory,
            ]),
        ]);

        $response->assertRedirect(route('categories.subcategories.vendor-links', [
            'category' => $sourceCategory,
            'subcategory' => $subcategory,
        ]));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('vendor_categories', [
            'id' => $link->id,
            'vendor_id' => $vendor->id,
            'category_id' => $targetCategory->id,
            'subcategory_id' => $targetSub->id,
        ]);
        $this->assertSame($sourceCategory->id, $procurementRequest->fresh()->category_id);
        $this->assertSame($subcategory->id, $procurementRequest->fresh()->subcategory_id);
    }

    public function test_reassign_merges_when_vendor_already_linked_to_target(): void
    {
        [$sourceCategory, $targetCategory, $subcategory, $vendor, $link] = $this->seedLinkScenario();

        $targetSub = Subcategory::query()->create([
            'category_id' => $sourceCategory->id,
            'name_en' => 'Target Sub',
            'name_ar' => 'فرعي هدف',
            'slug' => 'target-sub-merge',
            'status' => 'active',
        ]);

        VendorCategory::query()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $sourceCategory->id,
            'subcategory_id' => $targetSub->id,
            'is_primary' => false,
        ]);

        $response = $this->actingAs($this->admin)->put(route('vendor-categories.reassign', $link), [
            'target_category_id' => $sourceCategory->id,
            'target_subcategory_id' => $targetSub->id,
            'return_url' => route('categories.subcategories.vendor-links', [
                'category' => $sourceCategory,
                'subcategory' => $subcategory,
            ]),
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('vendor_categories', ['id' => $link->id]);
        $this->assertDatabaseHas('vendor_categories', [
            'vendor_id' => $vendor->id,
            'category_id' => $sourceCategory->id,
            'subcategory_id' => $targetSub->id,
        ]);
    }

    public function test_remove_vendor_link_empties_subcategory(): void
    {
        [$sourceCategory, , $subcategory, $vendor, $link] = $this->seedLinkScenario();

        $response = $this->actingAs($this->admin)->delete(route('vendor-categories.destroy', $link), [
            'return_url' => route('categories.subcategories.vendor-links', [
                'category' => $sourceCategory,
                'subcategory' => $subcategory,
            ]),
        ]);

        $response->assertRedirect(route('categories.subcategories.vendor-links', [
            'category' => $sourceCategory,
            'subcategory' => $subcategory,
        ]));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('vendor_categories', ['id' => $link->id]);
        $this->assertDatabaseHas('vendors', ['id' => $vendor->id]);
    }

    public function test_reassign_can_update_matching_brochures(): void
    {
        [$sourceCategory, $targetCategory, $subcategory, $vendor, $link] = $this->seedLinkScenario();

        $targetSub = Subcategory::query()->create([
            'category_id' => $targetCategory->id,
            'name_en' => 'Target Sub',
            'name_ar' => 'فرعي هدف',
            'slug' => 'target-sub-brochure',
            'status' => 'active',
        ]);

        $brochure = VendorBrochure::query()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $sourceCategory->id,
            'subcategory_id' => $subcategory->id,
            'file_name' => 'brochure.pdf',
            'file_path' => 'vendors/brochure.pdf',
        ]);

        app(CategoryVendorLinkService::class)->reassign(
            $link,
            $targetCategory->id,
            $targetSub->id,
            updateBrochures: true,
        );

        $this->assertSame($targetCategory->id, $brochure->fresh()->category_id);
        $this->assertSame($targetSub->id, $brochure->fresh()->subcategory_id);
    }

    /**
     * @return array{0: Category, 1: Category, 2: Subcategory, 3: Vendor, 4: VendorCategory}
     */
    private function seedLinkScenario(): array
    {
        $sourceCategory = Category::query()->create([
            'name_en' => 'Source Category',
            'name_ar' => 'تصنيف مصدر',
            'slug' => 'source-category-link',
            'status' => 'active',
        ]);

        $targetCategory = Category::query()->create([
            'name_en' => 'Target Category',
            'name_ar' => 'تصنيف هدف',
            'slug' => 'target-category-link',
            'status' => 'active',
        ]);

        $subcategory = Subcategory::query()->create([
            'category_id' => $sourceCategory->id,
            'name_en' => 'Source Sub',
            'name_ar' => 'فرعي مصدر',
            'slug' => 'source-sub-link',
            'status' => 'active',
        ]);

        $vendor = Vendor::query()->create([
            'vendor_code' => 'V-LINK-001',
            'name' => 'Link Test Vendor',
            'language' => 'en',
            'status' => 'active',
        ]);

        $link = VendorCategory::query()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $sourceCategory->id,
            'subcategory_id' => $subcategory->id,
            'is_primary' => true,
        ]);

        return [$sourceCategory, $targetCategory, $subcategory, $vendor, $link];
    }
}
