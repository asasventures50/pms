<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('id')->constrained('users')->nullOnDelete();

            $table->string('vendor_company_name')->nullable()->after('vendor_id');
            $table->string('vendor_contact')->nullable();
            $table->string('vendor_email')->nullable();
            $table->string('vendor_phone')->nullable();
            $table->text('vendor_address')->nullable();

            $table->text('payment_terms')->nullable()->after('total_price');
            $table->string('delivery_time')->nullable();
            $table->text('delivery_location')->nullable();

            $table->string('procurement_signature')->nullable();
            $table->date('procurement_signed_at')->nullable();
            $table->string('finance_signature')->nullable();
            $table->date('finance_signed_at')->nullable();
            $table->string('ceo_signature')->nullable();
            $table->date('ceo_signed_at')->nullable();
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('title')->nullable()->change();
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('item', 100)->nullable();
            $table->text('description')->nullable();
            $table->decimal('quantity', 12, 3)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn([
                'vendor_company_name',
                'vendor_contact',
                'vendor_email',
                'vendor_phone',
                'vendor_address',
                'payment_terms',
                'delivery_time',
                'delivery_location',
                'procurement_signature',
                'procurement_signed_at',
                'finance_signature',
                'finance_signed_at',
                'ceo_signature',
                'ceo_signed_at',
            ]);
        });
    }
};
