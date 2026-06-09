<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->json('retentions')->nullable()->after('payment_terms');
            $table->boolean('show_retention')->default(true)->after('retentions');
            $table->boolean('primary_insurance_applicable')->nullable()->after('show_retention');
            $table->text('primary_insurance_requirements')->nullable()->after('primary_insurance_applicable');
            $table->boolean('final_insurance_applicable')->nullable()->after('primary_insurance_requirements');
            $table->text('final_insurance_requirements')->nullable()->after('final_insurance_applicable');
            $table->boolean('show_insurance')->default(true)->after('final_insurance_requirements');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn([
                'retentions',
                'show_retention',
                'primary_insurance_applicable',
                'primary_insurance_requirements',
                'final_insurance_applicable',
                'final_insurance_requirements',
                'show_insurance',
            ]);
        });
    }
};
