<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Re-create legacy insurance columns if a prior deploy dropped them.
     * Does not remove or overwrite maintenance columns.
     */
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_orders', 'primary_insurance_applicable')) {
                $table->boolean('primary_insurance_applicable')->nullable();
            }
            if (! Schema::hasColumn('purchase_orders', 'primary_insurance_requirements')) {
                $table->text('primary_insurance_requirements')->nullable();
            }
            if (! Schema::hasColumn('purchase_orders', 'final_insurance_applicable')) {
                $table->boolean('final_insurance_applicable')->nullable();
            }
            if (! Schema::hasColumn('purchase_orders', 'final_insurance_requirements')) {
                $table->text('final_insurance_requirements')->nullable();
            }
            if (! Schema::hasColumn('purchase_orders', 'show_insurance')) {
                $table->boolean('show_insurance')->default(true);
            }
        });
    }

    public function down(): void
    {
        // Intentionally empty — do not drop legacy insurance data on rollback.
    }
};
