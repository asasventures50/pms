<?php

namespace Tests\Unit;

use App\Services\Procurement\Rfqs\RfqGeneralTermsService;
use App\Support\Procurement\ProcurementScopeType;
use PHPUnit\Framework\TestCase;

class RfqGeneralTermsServiceTest extends TestCase
{
    public function test_normalize_texts_filters_empty_values(): void
    {
        $service = new RfqGeneralTermsService;

        $this->assertSame(
            ['First term', 'Second term'],
            $service->normalizeTexts([' First term ', '', 'Second term', '   '])
        );
    }

    public function test_normalize_line_terms_splits_multiline_text(): void
    {
        $service = new RfqGeneralTermsService;

        $this->assertSame(
            ['Warranty 24 months', 'On-site installation'],
            $service->normalizeLineTerms("Warranty 24 months\n\nOn-site installation\n")
        );
    }

    public function test_parse_stored_terms_splits_general_and_custom(): void
    {
        $service = new RfqGeneralTermsService;

        $parsed = $service->parseStoredTerms([
            'general' => ['General A'],
            'custom' => ['Custom B'],
        ]);

        $this->assertSame(['General A'], $parsed['general']);
        $this->assertSame(['Custom B'], $parsed['custom']);
        $this->assertSame(['General A', 'Custom B'], $parsed['all']);
    }

    public function test_build_terms_payload_normalizes_both_groups(): void
    {
        $service = new RfqGeneralTermsService;

        $payload = $service->buildTermsPayload([' General '], ['', ' Extra ']);

        $this->assertSame(['General'], $payload['general']);
        $this->assertSame(['Extra'], $payload['custom']);
    }

    public function test_order_scope_types_follows_catalog_order(): void
    {
        $service = new RfqGeneralTermsService;

        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('orderScopeTypes');
        $method->setAccessible(true);

        $ordered = $method->invoke($service, [
            ProcurementScopeType::Studies,
            ProcurementScopeType::Supply,
            'Invalid',
        ]);

        $this->assertSame([
            ProcurementScopeType::Supply,
            ProcurementScopeType::Studies,
        ], $ordered);
    }
}
