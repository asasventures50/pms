<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Access\PermissionCatalog;
use Database\Seeders\Access\RolePermissionSeeder;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
