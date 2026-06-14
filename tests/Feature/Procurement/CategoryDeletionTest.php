<?php

namespace Tests\Feature\Procurement;

use App\Models\Procurement\Vendors\Category;
use App\Models\Procurement\Vendors\Subcategory;
use App\Models\Procurement\Vendors\Vendor;
use App\Models\Procurement\Vendors\VendorCategory;
use App\Models\User;
use Database\Seeders\Access\RolePermissionSeeder;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryDeletionTest extends TestCase
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

    public function test_category_can_be_deleted_when_not_linked_to_vendors(): void
    {
        $category = Category::query()->create([
            'name_en' => 'Empty Category',
            'name_ar' => 'تصنيف فارغ',
            'slug' => 'empty-category',
            'status' => 'active',
        ]);

        Subcategory::query()->create([
            'category_id' => $category->id,
            'name_en' => 'Empty Sub',
            'name_ar' => 'فرعي فارغ',
            'slug' => 'empty-sub',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('success');
        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    public function test_category_cannot_be_deleted_when_linked_to_vendors(): void
    {
        [$category] = $this->seedCategoryWithVendorLink();

        $response = $this->actingAs($this->admin)->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'deleted_at' => null,
        ]);
    }

    public function test_subcategory_cannot_be_removed_from_edit_when_linked_to_vendors(): void
    {
        [$category, $subcategory] = $this->seedCategoryWithVendorLink();

        $response = $this->actingAs($this->admin)->from(route('categories.edit', $category))
            ->put(route('categories.update', $category), [
                'name_en' => $category->name_en,
                'name_ar' => $category->name_ar,
                'slug' => $category->slug,
                'status' => $category->status,
                'subcategories' => [],
            ]);

        $response->assertRedirect(route('categories.edit', $category));
        $response->assertSessionHasErrors('subcategories');
        $this->assertDatabaseHas('subcategories', [
            'id' => $subcategory->id,
            'deleted_at' => null,
        ]);
    }

    public function test_subcategory_can_be_removed_when_not_linked_to_vendors(): void
    {
        $category = Category::query()->create([
            'name_en' => 'Removable Category',
            'name_ar' => 'تصنيف قابل للحذف',
            'slug' => 'removable-category',
            'status' => 'active',
        ]);

        $subcategory = Subcategory::query()->create([
            'category_id' => $category->id,
            'name_en' => 'Removable Sub',
            'name_ar' => 'فرعي قابل للحذف',
            'slug' => 'removable-sub',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->put(route('categories.update', $category), [
            'name_en' => $category->name_en,
            'name_ar' => $category->name_ar,
            'slug' => $category->slug,
            'status' => $category->status,
            'subcategories' => [],
        ]);

        $response->assertRedirect(route('categories.show', $category));
        $response->assertSessionHas('success');
        $this->assertSoftDeleted('subcategories', ['id' => $subcategory->id]);
    }

    /**
     * @return array{0: Category, 1: Subcategory}
     */
    private function seedCategoryWithVendorLink(): array
    {
        $category = Category::query()->create([
            'name_en' => 'Linked Category',
            'name_ar' => 'تصنيف مربوط',
            'slug' => 'linked-category',
            'status' => 'active',
        ]);

        $subcategory = Subcategory::query()->create([
            'category_id' => $category->id,
            'name_en' => 'Linked Sub',
            'name_ar' => 'فرعي مربوط',
            'slug' => 'linked-sub',
            'status' => 'active',
        ]);

        $vendor = Vendor::query()->create([
            'vendor_code' => 'V-LINK-001',
            'name' => 'Linked Vendor',
            'language' => 'en',
            'status' => 'active',
        ]);

        VendorCategory::query()->create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
            'is_primary' => true,
        ]);

        return [$category, $subcategory];
    }
}
