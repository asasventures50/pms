<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('vendor_code', 100)->nullable()->after('vendor_id');
            $table->string('vendor_language', 10)->nullable()->after('vendor_code');
            $table->text('vendor_description')->nullable()->after('vendor_language');
            $table->text('vendor_profile_notes')->nullable()->after('vendor_description');

            $table->string('vendor_whatsapp', 50)->nullable()->after('vendor_phone');
            $table->string('vendor_telegram', 100)->nullable()->after('vendor_whatsapp');
            $table->string('vendor_company_email')->nullable()->after('vendor_telegram');
            $table->string('vendor_website')->nullable()->after('vendor_company_email');

            $table->string('vendor_primary_contact_position')->nullable()->after('vendor_contact');
            $table->string('vendor_primary_contact_phone', 50)->nullable()->after('vendor_primary_contact_position');
            $table->string('vendor_primary_contact_email')->nullable()->after('vendor_primary_contact_phone');

            $table->string('vendor_company_type', 50)->nullable()->after('vendor_address');
            $table->string('vendor_coverage_type', 50)->nullable()->after('vendor_company_type');
            $table->string('vendor_tax_number', 100)->nullable()->after('vendor_coverage_type');
            $table->string('vendor_registration_number', 100)->nullable()->after('vendor_tax_number');
            $table->string('vendor_license_number', 100)->nullable()->after('vendor_registration_number');
            $table->text('vendor_business_types')->nullable()->after('vendor_license_number');
            $table->text('vendor_categories_summary')->nullable()->after('vendor_business_types');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn([
                'vendor_code',
                'vendor_language',
                'vendor_description',
                'vendor_profile_notes',
                'vendor_whatsapp',
                'vendor_telegram',
                'vendor_company_email',
                'vendor_website',
                'vendor_primary_contact_position',
                'vendor_primary_contact_phone',
                'vendor_primary_contact_email',
                'vendor_company_type',
                'vendor_coverage_type',
                'vendor_tax_number',
                'vendor_registration_number',
                'vendor_license_number',
                'vendor_business_types',
                'vendor_categories_summary',
            ]);
        });
    }
};
