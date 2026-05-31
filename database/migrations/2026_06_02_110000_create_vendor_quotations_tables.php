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

        Schema::create('vendor_quotations', function (Blueprint $table) use ($isMysql) {
            if ($isMysql) {
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
            }

            $table->id();
            $table->foreignId('rfq_id')->constrained('rfqs')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('quotation_number', 120)->unique();

            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->string('vendor_company_name')->nullable();
            $table->string('vendor_contact')->nullable();
            $table->string('vendor_email')->nullable();
            $table->string('vendor_phone')->nullable();
            $table->text('vendor_address')->nullable();

            $table->decimal('grand_total', 12, 2)->default(0);
            $table->string('payment_method')->nullable();
            $table->text('notes')->nullable();
            $table->json('documents_attached')->nullable();

            $table->string('vendor_rep_name')->nullable();
            $table->string('vendor_rep_signature')->nullable();
            $table->date('vendor_rep_signed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('vendor_quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_quotation_id')->constrained('vendor_quotations')->cascadeOnDelete();
            $table->foreignId('rfq_item_id')->nullable()->constrained('rfq_items')->nullOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('item_number', 100)->nullable();
            $table->string('compliance', 50)->nullable();
            $table->string('alternative_if_no')->nullable();
            $table->text('item_description_if_no')->nullable();
            $table->string('brand_origin')->nullable();
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->string('currency', 10)->nullable();
            $table->decimal('total_price', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->string('lead_time')->nullable();
            $table->string('warranty')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_quotation_items');
        Schema::dropIfExists('vendor_quotations');
    }
};
