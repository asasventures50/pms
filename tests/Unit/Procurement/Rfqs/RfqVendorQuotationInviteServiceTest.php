<?php

namespace Tests\Unit\Procurement\Rfqs;

use App\Enums\Procurement\Rfqs\RfqVendorQuotationInviteLocale;
use App\Models\Procurement\Rfqs\RfqVendorQuotationInvite;
use App\Services\Procurement\Rfqs\RfqGeneralTermsService;
use App\Services\Procurement\Rfqs\RfqVendorQuotationInviteService;
use App\Services\Procurement\VendorQuotations\VendorQuotationCodeGenerator;
use App\Services\Procurement\VendorQuotations\VendorQuotationPersistenceService;
use Mockery;
use Tests\TestCase;

class RfqVendorQuotationInviteServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_resolve_terms_returns_empty_when_include_terms_is_false(): void
    {
        $termsService = Mockery::mock(RfqGeneralTermsService::class);
        $termsService->shouldNotReceive('scopeTypesFromNormalizedItems');
        $termsService->shouldNotReceive('activeTextsForScopeTypes');

        $service = new RfqVendorQuotationInviteService(
            Mockery::mock(VendorQuotationPersistenceService::class),
            Mockery::mock(VendorQuotationCodeGenerator::class),
            $termsService,
        );

        $invite = new RfqVendorQuotationInvite([
            'include_terms' => false,
        ]);

        $this->assertSame([], $service->resolveTermsForInvite($invite, 'en'));
    }

    public function test_locale_enum_locks_ar_and_en_only(): void
    {
        $this->assertTrue(RfqVendorQuotationInviteLocale::Ar->locksLocale());
        $this->assertTrue(RfqVendorQuotationInviteLocale::En->locksLocale());
        $this->assertFalse(RfqVendorQuotationInviteLocale::VendorChoice->locksLocale());
        $this->assertSame('ar', RfqVendorQuotationInviteLocale::Ar->lockedLocale());
        $this->assertNull(RfqVendorQuotationInviteLocale::VendorChoice->lockedLocale());
    }
}
