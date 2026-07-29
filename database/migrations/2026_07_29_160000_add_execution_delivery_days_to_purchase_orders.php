<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_orders', 'execution_delivery_days')) {
                $table->unsignedSmallInteger('execution_delivery_days')->nullable()->after('dismantling_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_orders', 'execution_delivery_days')) {
                $table->dropColumn('execution_delivery_days');
            }
        });
    }
};
