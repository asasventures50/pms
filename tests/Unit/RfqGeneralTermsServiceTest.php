<?php

namespace Tests\Unit;

use App\Enums\Procurement\Rfqs\RfqTermsLocale;
use App\Models\Procurement\Rfqs\RfqGeneralTerm;
use App\Services\Procurement\Rfqs\RfqGeneralTermsService;
use App\Support\Procurement\ProcurementScopeType;
use App\Support\Procurement\RfqTerms;
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

    public function test_resolve_stored_terms_for_locale_merges_general_and_custom(): void
    {
        $service = new RfqGeneralTermsService;

        $resolved = $service->resolveStoredTermsForLocale([
            'general' => ['General A'],
            'custom' => ['Custom B'],
        ]);

        $this->assertSame(['General A', 'Custom B'], $resolved);
    }

    public function test_resolve_stored_custom_terms_for_locale_returns_only_custom(): void
    {
        $service = new RfqGeneralTermsService;

        $custom = $service->resolveStoredCustomTermsForLocale([
            'general' => ['General A'],
            'custom' => ['Custom B'],
        ]);

        $this->assertSame(['Custom B'], $custom);
    }

    public function test_scope_types_from_order_term_dates(): void
    {
        $service = new RfqGeneralTermsService;

        $this->assertSame(
            ['Maintenance', 'Dismantling'],
            $service->scopeTypesFromOrderTermDates('2026-01-01', '2026-02-01')
        );
        $this->assertSame([], $service->scopeTypesFromOrderTermDates(null, null));
    }

    public function test_merge_purchase_order_scope_types_includes_pr_and_date_scopes(): void
    {
        $service = new RfqGeneralTermsService;

        $merged = $service->mergePurchaseOrderScopeTypes(
            [ProcurementScopeType::Contractor, ProcurementScopeType::Supplier],
            ['Maintenance'],
        );

        $this->assertSame([
            ProcurementScopeType::Contractor,
            ProcurementScopeType::Supplier,
            'Maintenance',
        ], $merged);
    }

    public function test_scope_types_from_linked_procurement_request_without_pr_returns_date_scopes_only(): void
    {
        $service = new RfqGeneralTermsService;

        $this->assertSame(
            ['Maintenance'],
            $service->scopeTypesFromLinkedProcurementRequest(null, ['PR-001-01'], '2026-01-01', null)
        );
    }

    public function test_sections_for_print_groups_by_scope_when_unfiltered(): void
    {
        $service = new RfqGeneralTermsService;
        $terms = collect([
            new RfqGeneralTerm(['scope_types' => null, 'body_en' => 'Global']),
            new RfqGeneralTerm(['scope_types' => ['Supplier'], 'body_en' => 'Supply']),
        ]);

        $sections = $service->sectionsForPrint($terms, null);

        $this->assertCount(2, $sections);
        $this->assertSame('General (all RFQs)', $sections[0]['label']);
        $this->assertSame('Global', $sections[0]['terms']->first()->body_en);
        $this->assertSame('Supplier', $sections[1]['label']);
    }

    public function test_sections_for_print_single_section_when_scope_filtered(): void
    {
        $service = new RfqGeneralTermsService;
        $terms = collect([
            new RfqGeneralTerm(['scope_types' => null, 'body_en' => 'Global']),
        ]);

        $sections = $service->sectionsForPrint($terms, RfqGeneralTermsService::GLOBAL_SCOPE_KEY);

        $this->assertCount(1, $sections);
        $this->assertSame('General (all RFQs) only', $sections[0]['label']);
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

    public function test_resolve_body_prefers_locale_with_fallback(): void
    {
        $service = new RfqGeneralTermsService;
        $term = new RfqGeneralTerm([
            'body_ar' => 'نص عربي',
            'body_en' => 'English text',
        ]);

        $this->assertSame('نص عربي', $service->resolveBody($term, RfqTermsLocale::Ar->value));
        $this->assertSame('English text', $service->resolveBody($term, RfqTermsLocale::En->value));

        $term->body_ar = null;
        $this->assertSame('English text', $service->resolveBody($term, RfqTermsLocale::Ar->value));
    }

    public function test_legacy_defaults_return_arabic_when_requested(): void
    {
        $arabic = RfqTerms::legacyDefaults(RfqTermsLocale::Ar->value);
        $english = RfqTerms::legacyDefaults(RfqTermsLocale::En->value);

        $this->assertNotSame($english[0], $arabic[0]);
        $this->assertCount(count($english), $arabic);
    }

    public function test_scope_types_label_lists_multiple_types(): void
    {
        $label = RfqGeneralTermsService::scopeTypesLabel([
            ProcurementScopeType::Installation,
            ProcurementScopeType::Supply,
        ]);

        $this->assertSame('Supply, Installation', $label);
    }

    public function test_scope_types_label_for_global_terms(): void
    {
        $this->assertSame(
            'General (all RFQs)',
            RfqGeneralTermsService::scopeTypesLabel(null)
        );
    }

    public function test_rfq_general_term_applies_to_each_selected_scope_type(): void
    {
        $term = new RfqGeneralTerm([
            'scope_types' => [ProcurementScopeType::Supply, ProcurementScopeType::Service],
        ]);

        $this->assertTrue($term->appliesToScopeType(ProcurementScopeType::Supply));
        $this->assertTrue($term->appliesToScopeType(ProcurementScopeType::Service));
        $this->assertFalse($term->appliesToScopeType(ProcurementScopeType::Studies));
        $this->assertFalse($term->isGlobal());
    }
}
