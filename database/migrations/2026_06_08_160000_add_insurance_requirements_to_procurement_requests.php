<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('procurement_requests', 'primary_insurance_requirements')) {
            Schema::table('procurement_requests', function (Blueprint $table) {
                $table->text('primary_insurance_requirements')->nullable()->after('primary_insurance_applicable');
            });
        }

        if (! Schema::hasColumn('procurement_requests', 'final_insurance_requirements')) {
            Schema::table('procurement_requests', function (Blueprint $table) {
                $table->text('final_insurance_requirements')->nullable()->after('final_insurance_applicable');
            });
        }
    }

    public function down(): void
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            if (Schema::hasColumn('procurement_requests', 'primary_insurance_requirements')) {
                $table->dropColumn('primary_insurance_requirements');
            }
            if (Schema::hasColumn('procurement_requests', 'final_insurance_requirements')) {
                $table->dropColumn('final_insurance_requirements');
            }
        });
    }
};
