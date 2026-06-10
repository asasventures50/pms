<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add maintenance fields for P.O. display/import from P.R.
     * Legacy insurance columns are kept for historical data (no longer shown in UI).
     */
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_orders', 'after_sale_service_applicable')) {
                $table->boolean('after_sale_service_applicable')->nullable();
            }
            if (! Schema::hasColumn('purchase_orders', 'warranty_years')) {
                $table->decimal('warranty_years', 4, 1)->nullable();
            }
            if (! Schema::hasColumn('purchase_orders', 'warranty_coverage')) {
                $table->text('warranty_coverage')->nullable();
            }
            if (! Schema::hasColumn('purchase_orders', 'show_maintenance')) {
                $table->boolean('show_maintenance')->default(true);
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $columns = [];

            foreach (['after_sale_service_applicable', 'warranty_years', 'warranty_coverage', 'show_maintenance'] as $column) {
                if (Schema::hasColumn('purchase_orders', $column)) {
                    $columns[] = $column;
                }
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
