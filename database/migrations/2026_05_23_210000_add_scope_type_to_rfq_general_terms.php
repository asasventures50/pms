<?php

use App\Support\Procurement\ProcurementScopeType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rfq_general_terms', function (Blueprint $table) {
            $table->string('scope_type', 100)->default(ProcurementScopeType::Supply)->after('id');
        });

        DB::table('rfq_general_terms')->update([
            'scope_type' => ProcurementScopeType::Supply,
        ]);

        Schema::table('rfq_general_terms', function (Blueprint $table) {
            $table->index(['scope_type', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::table('rfq_general_terms', function (Blueprint $table) {
            $table->dropIndex(['scope_type', 'is_active', 'sort_order']);
            $table->dropColumn('scope_type');
        });
    }
};
