<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $isMysql = DB::getDriverName() === 'mysql';

        Schema::create('invoices', function (Blueprint $table) use ($isMysql) {
            if ($isMysql) {
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
            }

            $table->id();
            $table->string('invoice_number', 100)->unique();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('recipient_name', 255);
            $table->date('invoiced_at');
            $table->string('po_number', 500);
            $table->string('vendor_company_name', 255)->nullable();
            $table->string('currency_code', 3)->nullable();
            $table->decimal('transport_fees', 12, 2)->default(0);
            $table->decimal('supervision_fees', 12, 2)->default(0);
            $table->decimal('administrative_fees', 12, 2)->default(0);
            $table->decimal('logistics_fees', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);
            $table->boolean('merged_lines')->default(false);
            $table->timestamps();
        });

        Schema::create('invoice_purchase_orders', function (Blueprint $table) use ($isMysql) {
            if ($isMysql) {
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
            }

            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['invoice_id', 'purchase_order_id']);
        });

        Schema::create('invoice_items', function (Blueprint $table) use ($isMysql) {
            if ($isMysql) {
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
            }

            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->unsignedSmallInteger('line_number');
            $table->text('description');
            $table->decimal('quantity', 12, 3)->default(0);
            $table->string('unit', 50)->nullable();
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->json('source_purchase_order_item_ids')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoice_purchase_orders');
        Schema::dropIfExists('invoices');
    }
};
