<?php

use App\Support\Procurement\RfqTerms;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rfq_general_terms', function (Blueprint $table) {
            $table->string('scope_type', 100)->nullable()->default(null)->change();
        });

        $legacy = RfqTerms::legacyDefaults();

        DB::table('rfq_general_terms')
            ->whereIn('body', $legacy)
            ->whereNotNull('scope_type')
            ->delete();

        $now = now();

        foreach ($legacy as $index => $body) {
            DB::table('rfq_general_terms')->updateOrInsert(
                ['scope_type' => null, 'body' => $body],
                [
                    'sort_order' => $index,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::table('rfq_general_terms', function (Blueprint $table) {
            $table->string('scope_type', 100)->default('Supplier')->nullable(false)->change();
        });
    }
};
