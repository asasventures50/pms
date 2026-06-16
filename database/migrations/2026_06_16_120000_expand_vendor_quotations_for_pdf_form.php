<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_quotations', function (Blueprint $table) {
            $table->date('quotation_valid_until')->nullable()->after('payment_method');
            $table->text('after_sales_service')->nullable()->after('quotation_valid_until');
            $table->decimal('delivery_charges', 12, 2)->nullable()->after('grand_total');
            $table->decimal('installation_charges', 12, 2)->nullable()->after('delivery_charges');
            $table->decimal('total_discount', 12, 2)->nullable()->after('installation_charges');
            $table->boolean('price_includes_delivery')->nullable()->after('total_discount');
            $table->boolean('price_includes_installation')->nullable()->after('price_includes_delivery');
            $table->string('vendor_rep_job_title')->nullable()->after('vendor_rep_name');
            $table->string('vendor_rep_email')->nullable()->after('vendor_rep_job_title');
            $table->string('vendor_rep_phone', 50)->nullable()->after('vendor_rep_email');
            $table->json('vendor_declarations')->nullable()->after('vendor_rep_signed_at');
        });

        Schema::table('vendor_quotation_items', function (Blueprint $table) {
            $table->decimal('quantity_quoted', 12, 3)->nullable()->after('item_number');
            $table->string('brand')->nullable()->after('brand_origin');
            $table->string('model')->nullable()->after('brand');
            $table->string('country_of_origin')->nullable()->after('model');
            $table->decimal('discount', 12, 2)->nullable()->after('total_price');
            $table->decimal('tax_rate', 5, 2)->nullable()->after('discount');
            $table->decimal('delivery_charges', 12, 2)->nullable()->after('tax');
            $table->decimal('installation', 12, 2)->nullable()->after('delivery_charges');
            $table->text('remarks')->nullable()->after('warranty');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_quotation_items', function (Blueprint $table) {
            $table->dropColumn([
                'quantity_quoted',
                'brand',
                'model',
                'country_of_origin',
                'discount',
                'tax_rate',
                'delivery_charges',
                'installation',
                'remarks',
            ]);
        });

        Schema::table('vendor_quotations', function (Blueprint $table) {
            $table->dropColumn([
                'quotation_valid_until',
                'after_sales_service',
                'delivery_charges',
                'installation_charges',
                'total_discount',
                'price_includes_delivery',
                'price_includes_installation',
                'vendor_rep_job_title',
                'vendor_rep_email',
                'vendor_rep_phone',
                'vendor_declarations',
            ]);
        });
    }
};
