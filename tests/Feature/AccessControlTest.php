<?php

namespace Tests\Feature;

use App\Models\Access\Permission;
use App\Models\Access\Role;
use App\Models\User;
use App\Support\Access\PermissionCatalog;
use Database\Seeders\Access\RolePermissionSeeder;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(AdminUserSeeder::class);
    }

    public function test_super_admin_can_access_users_index(): void
    {
        $user = User::query()->where('email', 'pms@tamkeensy.com')->firstOrFail();

        $response = $this->actingAs($user)->get(route('users.index'));

        $response->assertOk();
    }

    public function test_user_without_roles_cannot_access_users_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('users.index'));

        $response->assertForbidden();
    }

    public function test_procurement_officer_cannot_access_users_index(): void
    {
        $user = User::factory()->create();
        $user->syncRoles([PermissionCatalog::PROCUREMENT_OFFICER_ROLE]);

        $response = $this->actingAs($user)->get(route('users.index'));

        $response->assertForbidden();
    }

    public function test_procurement_officer_can_access_vendors_index(): void
    {
        $user = User::factory()->create();
        $user->syncRoles([PermissionCatalog::PROCUREMENT_OFFICER_ROLE]);

        $response = $this->actingAs($user)->get(route('vendors.index'));

        $response->assertOk();
    }

    public function test_view_only_role_can_access_procurement_requests_index(): void
    {
        $role = Role::query()->create(['name' => 'pr-view', 'label' => 'PR View']);
        $role->syncPermissions(['procurement-requests.view']);

        $user = User::factory()->create();
        $user->syncRoles(['pr-view']);

        $this->assertTrue($user->hasPermission('procurement-requests.view'));

        $response = $this->actingAs($user)->get(route('procurement-requests.index'));

        $response->assertOk();
    }

    public function test_create_only_role_can_access_procurement_requests_index_and_create_form(): void
    {
        $role = Role::query()->create(['name' => 'pr-create', 'label' => 'PR Create']);
        $role->syncPermissions(['procurement-requests.create']);

        $user = User::factory()->create();
        $user->syncRoles(['pr-create']);

        $this->assertTrue($user->hasPermission('procurement-requests.create'));
        $this->assertTrue($user->hasPermission('procurement-requests.view'));

        $this->actingAs($user)->get(route('procurement-requests.index'))->assertOk();
        $this->actingAs($user)->get(route('procurement-requests.create'))->assertOk();
    }

    public function test_legacy_view_all_permission_is_recognized_as_view(): void
    {
        $legacy = Permission::query()->create([
            'name' => 'procurement-requests.view-all',
            'label' => 'View all procurement requests',
            'group' => 'Procurement requests',
        ]);

        $role = Role::query()->create(['name' => 'pr-legacy-view', 'label' => 'PR Legacy View']);
        $role->permissions()->sync([$legacy->id]);

        $user = User::factory()->create();
        $user->syncRoles(['pr-legacy-view']);

        $this->assertTrue($user->hasPermission('procurement-requests.view'));

        $this->actingAs($user)->get(route('procurement-requests.index'))->assertOk();
    }

    /**
     * @return array<string, array{route: string, permission: string}>
     */
    public static function viewOnlyIndexRoutesProvider(): array
    {
        return [
            'PR' => ['route' => 'procurement-requests.index', 'permission' => 'procurement-requests.view'],
            'PO' => ['route' => 'purchase-orders.index', 'permission' => 'purchase-orders.view'],
            'Vendors' => ['route' => 'vendors.index', 'permission' => 'vendors.view'],
            'Categories' => ['route' => 'categories.index', 'permission' => 'categories.view'],
            'RFQs' => ['route' => 'rfqs.index', 'permission' => 'rfqs.view'],
            'Projects' => ['route' => 'projects.index', 'permission' => 'projects.view'],
            'Locations' => ['route' => 'locations.index', 'permission' => 'locations.view'],
            'Users' => ['route' => 'users.index', 'permission' => 'users.view'],
            'Roles' => ['route' => 'roles.index', 'permission' => 'roles.view'],
        ];
    }

    #[DataProvider('viewOnlyIndexRoutesProvider')]
    public function test_view_only_permission_grants_index_access(string $route, string $permission): void
    {
        $role = Role::query()->create([
            'name' => 'view-'.str_replace('.', '-', $permission),
            'label' => 'View '.$permission,
        ]);
        $role->syncPermissions([$permission]);

        $user = User::factory()->create();
        $user->syncRoles([$role->name]);

        $this->actingAs($user)->get(route($route))->assertOk();
    }
}
