<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('procurement_requests')) {
            return;
        }

        Schema::table('procurement_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('procurement_requests', 'compliance_prequalification_level')) {
                $table->string('compliance_prequalification_level', 10)
                    ->nullable()
                    ->after('compliance_prequalification_required');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('procurement_requests')) {
            return;
        }

        Schema::table('procurement_requests', function (Blueprint $table) {
            if (Schema::hasColumn('procurement_requests', 'compliance_prequalification_level')) {
                $table->dropColumn('compliance_prequalification_level');
            }
        });
    }
};
