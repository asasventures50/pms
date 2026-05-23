<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProcurementAccessTest extends TestCase
{
    public function test_guest_is_redirected_from_dashboard_to_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login', absolute: false));
    }

    public function test_guest_is_redirected_from_vendors_to_login(): void
    {
        $response = $this->get('/vendors');

        $response->assertRedirect(route('login', absolute: false));
    }

    public function test_guest_is_redirected_from_categories_to_login(): void
    {
        $response = $this->get('/categories');

        $response->assertRedirect(route('login', absolute: false));
    }

    public function test_guest_is_redirected_from_rfq_terms_to_login(): void
    {
        $response = $this->get('/rfq-terms');

        $response->assertRedirect(route('login', absolute: false));
    }
}
