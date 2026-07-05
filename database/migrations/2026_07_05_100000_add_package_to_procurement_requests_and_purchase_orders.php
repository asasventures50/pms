<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('procurement_requests', 'package')) {
                $table->string('package', 500)->nullable()->after('project_id');
            }
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_orders', 'package')) {
                $table->string('package', 500)->nullable()->after('procurement_request_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            if (Schema::hasColumn('procurement_requests', 'package')) {
                $table->dropColumn('package');
            }
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_orders', 'package')) {
                $table->dropColumn('package');
            }
        });
    }
};
