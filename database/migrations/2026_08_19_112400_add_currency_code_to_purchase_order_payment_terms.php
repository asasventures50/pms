<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_payment_terms', function (Blueprint $table) {
            $table->string('currency_code', 3)->nullable()->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_payment_terms', function (Blueprint $table) {
            $table->dropColumn('currency_code');
        });
    }
};
