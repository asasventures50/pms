<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rfqs', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('created_by');
            $table->string('company_phone', 50)->nullable();
            $table->string('company_email')->nullable();
            $table->text('company_address')->nullable();
            $table->string('company_website')->nullable();
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('company_website')->nullable()->after('company_email');
        });
    }

    public function down(): void
    {
        Schema::table('rfqs', function (Blueprint $table) {
            $table->dropColumn([
                'company_name',
                'company_phone',
                'company_email',
                'company_address',
                'company_website',
            ]);
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('company_website');
        });
    }
};
