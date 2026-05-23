<?php

use App\Models\Procurement\Rfqs\RfqGeneralTerm;
use App\Support\Procurement\RfqTerms;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rfq_general_terms', function (Blueprint $table) {
            $table->string('scope_type', 100)->nullable()->default(null)->change();
        });

        $legacy = RfqTerms::legacyDefaults();

        RfqGeneralTerm::query()
            ->whereIn('body', $legacy)
            ->whereNotNull('scope_type')
            ->delete();

        foreach ($legacy as $index => $body) {
            RfqGeneralTerm::query()->firstOrCreate(
                ['scope_type' => null, 'body' => $body],
                [
                    'sort_order' => $index,
                    'is_active' => true,
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::table('rfq_general_terms', function (Blueprint $table) {
            $table->string('scope_type', 100)->default('Supply')->nullable(false)->change();
        });
    }
};
