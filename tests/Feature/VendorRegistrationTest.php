<?php

namespace Tests\Feature;

use App\Enums\Procurement\Vendors\VendorStatus;
use App\Models\Procurement\Vendors\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_vendor_registration_form(): void
    {
        $response = $this->get(route('vendor-registration.create'));

        $response->assertOk();
        $response->assertSee('Register as Vendor', false);
        $response->assertSee('Submit registration', false);
    }

    public function test_guest_can_submit_vendor_registration(): void
    {
        $response = $this->post(route('vendor-registration.store'), [
            'name' => 'Public Vendor Co.',
            'language' => 'en',
        ]);

        $response->assertRedirect(route('vendor-registration.thanks'));

        $vendor = Vendor::query()->where('name', 'Public Vendor Co.')->first();

        $this->assertNotNull($vendor);
        $this->assertSame(VendorStatus::PendingReview, $vendor->status);
        $this->assertNull($vendor->created_by);
        $this->assertNotEmpty($vendor->vendor_code);
    }

    public function test_guest_is_redirected_from_vendors_index_to_login(): void
    {
        $response = $this->get('/vendors');

        $response->assertRedirect(route('login', absolute: false));
    }
}
