<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rfq_general_terms', function (Blueprint $table) {
            $table->json('scope_types')->nullable()->after('id');
        });

        foreach (DB::table('rfq_general_terms')->select('id', 'scope_type')->get() as $row) {
            $scopeTypes = $row->scope_type !== null && $row->scope_type !== ''
                ? json_encode([(string) $row->scope_type])
                : null;

            DB::table('rfq_general_terms')->where('id', $row->id)->update([
                'scope_types' => $scopeTypes,
            ]);
        }

        Schema::table('rfq_general_terms', function (Blueprint $table) {
            $table->dropIndex(['scope_type', 'is_active', 'sort_order']);
            $table->dropColumn('scope_type');
        });
    }

    public function down(): void
    {
        Schema::table('rfq_general_terms', function (Blueprint $table) {
            $table->string('scope_type', 100)->nullable()->after('id');
        });

        foreach (DB::table('rfq_general_terms')->select('id', 'scope_types')->get() as $row) {
            $decoded = json_decode((string) ($row->scope_types ?? ''), true);
            $first = is_array($decoded) && $decoded !== [] ? (string) $decoded[0] : null;

            DB::table('rfq_general_terms')->where('id', $row->id)->update([
                'scope_type' => $first,
            ]);
        }

        Schema::table('rfq_general_terms', function (Blueprint $table) {
            $table->dropColumn('scope_types');
            $table->index(['scope_type', 'is_active', 'sort_order']);
        });
    }
};
