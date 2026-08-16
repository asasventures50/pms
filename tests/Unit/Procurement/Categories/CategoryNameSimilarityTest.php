<?php

namespace Tests\Unit\Procurement\Categories;

use App\Support\Procurement\Categories\CategoryNameSimilarity;
use PHPUnit\Framework\TestCase;

class CategoryNameSimilarityTest extends TestCase
{
    private CategoryNameSimilarity $similarity;

    protected function setUp(): void
    {
        parent::setUp();
        $this->similarity = new CategoryNameSimilarity;
    }

    public function test_exact_english_is_a_full_match(): void
    {
        $score = $this->similarity->score(
            ['name_en' => 'Mechanical Works', 'name_ar' => 'أ', 'slug' => 'mechanical-works'],
            ['name_en' => 'Mechanical Works', 'name_ar' => 'ب', 'slug' => 'other'],
        );

        $this->assertSame(100, $score);
    }

    public function test_same_arabic_with_renamed_english_is_a_strong_match(): void
    {
        $score = $this->similarity->score(
            [
                'name_ar' => 'أعمال الانشاءات والاكساءات',
                'name_en' => 'Premium Hardscape & Cladding',
                'slug' => 'premium-hardscape-cladding',
            ],
            [
                'name_ar' => 'أعمال الانشاءات والاكساءات',
                'name_en' => 'Construction, Cladding & Fitting-out works',
                'slug' => 'construction-cladding-fitting-out-works',
            ],
        );

        $this->assertGreaterThanOrEqual(90, $score);
    }

    public function test_flooring_matches_flooring_works(): void
    {
        $score = $this->similarity->score(
            ['name_ar' => 'أرضيات', 'name_en' => 'Flooring', 'slug' => 'flooring'],
            ['name_ar' => 'أعمال أرضيات', 'name_en' => 'Flooring Works', 'slug' => 'flooring-works'],
        );

        $this->assertGreaterThanOrEqual(55, $score);
    }

    public function test_unrelated_names_stay_below_suggest_threshold(): void
    {
        $score = $this->similarity->score(
            ['name_ar' => 'خدمات الشحن', 'name_en' => 'Freight & Shipping Services', 'slug' => 'freight-shipping-services'],
            ['name_ar' => 'أنظمة مكافحة الحرائق', 'name_en' => 'Firefighting Systems', 'slug' => 'firefighting-systems'],
        );

        $this->assertLessThan(55, $score);
    }
}
