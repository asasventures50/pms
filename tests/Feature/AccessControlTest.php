<?php

namespace Tests\Feature;

use App\Models\Access\Permission;
use App\Models\Access\Role;
use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
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

    public function test_view_own_role_sees_only_own_procurement_requests(): void
    {
        $role = Role::query()->create(['name' => 'pr-view-own', 'label' => 'PR View Own']);
        $role->syncPermissions(['procurement-requests.view-own']);

        $owner = User::factory()->create();
        $owner->syncRoles(['pr-view-own']);

        $otherUser = User::factory()->create();

        $ownRequest = ProcurementRequest::query()->create([
            'request_number' => 'PR-OWN-001',
            'created_by' => $owner->id,
        ]);

        $otherRequest = ProcurementRequest::query()->create([
            'request_number' => 'PR-OTHER-001',
            'created_by' => $otherUser->id,
        ]);

        $this->assertTrue($owner->hasPermission('procurement-requests.view-own'));
        $this->assertTrue($owner->hasPermission('procurement-requests.view'));
        $this->assertFalse($owner->canViewAllProcurementRequests());
        $this->assertTrue($owner->scopesProcurementRequestsToOwn());

        $index = $this->actingAs($owner)->get(route('procurement-requests.index'));
        $index->assertOk();
        $index->assertSee('PR-OWN-001');
        $index->assertDontSee('PR-OTHER-001');

        $this->actingAs($owner)->get(route('procurement-requests.show', $ownRequest))->assertOk();
        $this->actingAs($owner)->get(route('procurement-requests.show', $otherRequest))->assertForbidden();
    }

    public function test_view_all_role_sees_every_procurement_request(): void
    {
        $role = Role::query()->create(['name' => 'pr-view-all-role', 'label' => 'PR View All']);
        $role->syncPermissions(['procurement-requests.view']);

        $viewer = User::factory()->create();
        $viewer->syncRoles(['pr-view-all-role']);

        $creator = User::factory()->create();

        ProcurementRequest::query()->create([
            'request_number' => 'PR-VIEWER-001',
            'created_by' => $creator->id,
        ]);

        $this->assertTrue($viewer->canViewAllProcurementRequests());
        $this->assertFalse($viewer->scopesProcurementRequestsToOwn());

        $index = $this->actingAs($viewer)->get(route('procurement-requests.index'));
        $index->assertOk();
        $index->assertSee('PR-VIEWER-001');
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
        $this->assertTrue($user->canViewAllProcurementRequests());

        $this->actingAs($user)->get(route('procurement-requests.index'))->assertOk();
    }

    public function test_legacy_view_own_permission_is_scoped_to_creator(): void
    {
        $legacy = Permission::query()->where('name', 'procurement-requests.view-own')->firstOrFail();

        $role = Role::query()->create(['name' => 'pr-legacy-view-own', 'label' => 'PR Legacy View Own']);
        $role->permissions()->sync([$legacy->id]);

        $user = User::factory()->create();
        $user->syncRoles(['pr-legacy-view-own']);

        $otherUser = User::factory()->create();

        $ownRequest = ProcurementRequest::query()->create([
            'request_number' => 'PR-LEGACY-OWN',
            'created_by' => $user->id,
        ]);

        $otherRequest = ProcurementRequest::query()->create([
            'request_number' => 'PR-LEGACY-OTHER',
            'created_by' => $otherUser->id,
        ]);

        $this->assertTrue($user->hasPermission('procurement-requests.view-own'));
        $this->assertTrue($user->scopesProcurementRequestsToOwn());

        $this->actingAs($user)->get(route('procurement-requests.show', $ownRequest))->assertOk();
        $this->actingAs($user)->get(route('procurement-requests.show', $otherRequest))->assertForbidden();
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
