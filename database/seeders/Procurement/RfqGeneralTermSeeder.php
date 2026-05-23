<?php

namespace Database\Seeders\Procurement;

use App\Models\Procurement\Rfqs\RfqGeneralTerm;
use App\Support\Procurement\RfqTerms;
use Illuminate\Database\Seeder;

class RfqGeneralTermSeeder extends Seeder
{
    public function run(): void
    {
        if (RfqGeneralTerm::query()->whereNull('scope_type')->exists()) {
            return;
        }

        foreach (RfqTerms::legacyDefaults() as $index => $body) {
            RfqGeneralTerm::query()->create([
                'scope_type' => null,
                'body' => $body,
                'sort_order' => $index,
                'is_active' => true,
            ]);
        }
    }
}
