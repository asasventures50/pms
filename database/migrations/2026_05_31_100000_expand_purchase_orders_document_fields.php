<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('created_by');
            $table->text('company_address')->nullable();
            $table->string('company_phone', 50)->nullable();
            $table->string('company_email')->nullable();

            $table->string('delivery_contact_name')->nullable()->after('vendor_address');
            $table->string('delivery_contact_phone', 50)->nullable();
            $table->string('delivery_contact_email')->nullable();

            $table->decimal('delivery_fee', 12, 2)->default(0)->after('total_price');
            $table->decimal('discount', 12, 2)->default(0)->after('delivery_fee');

            $table->date('handover_at')->nullable()->after('delivery_location');
            $table->date('dismantling_at')->nullable();

            $table->json('terms')->nullable()->after('notes');
            $table->string('terms_locale', 5)->default('en')->after('terms');

            $table->string('vendor_signature')->nullable()->after('ceo_signed_at');
            $table->date('vendor_signed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn([
                'company_name',
                'company_address',
                'company_phone',
                'company_email',
                'delivery_contact_name',
                'delivery_contact_phone',
                'delivery_contact_email',
                'delivery_fee',
                'discount',
                'handover_at',
                'dismantling_at',
                'terms',
                'terms_locale',
                'vendor_signature',
                'vendor_signed_at',
            ]);
        });
    }
};
