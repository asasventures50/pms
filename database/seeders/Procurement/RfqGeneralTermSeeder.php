<?php

namespace Database\Seeders\Procurement;

use App\Models\Procurement\Rfqs\RfqGeneralTerm;
use App\Support\Procurement\RfqTerms;
use Illuminate\Database\Seeder;

class RfqGeneralTermSeeder extends Seeder
{
    public function run(): void
    {
        if (RfqGeneralTerm::query()->global()->exists()) {
            return;
        }

        $english = RfqTerms::legacyDefaultsEn();
        $arabic = RfqTerms::legacyDefaultsAr();

        foreach ($english as $index => $bodyEn) {
            RfqGeneralTerm::query()->create([
                'scope_types' => null,
                'body_en' => $bodyEn,
                'body_ar' => $arabic[$index] ?? null,
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }
    }
}
