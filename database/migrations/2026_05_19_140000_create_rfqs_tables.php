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

        Schema::create('rfqs', function (Blueprint $table) use ($isMysql) {
            if ($isMysql) {
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
            }

            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('rfq_number', 100)->unique();

            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->string('vendor_company_name')->nullable();
            $table->string('vendor_contact')->nullable();
            $table->string('vendor_email')->nullable();
            $table->string('vendor_phone')->nullable();
            $table->text('vendor_address')->nullable();

            $table->date('issue_date')->nullable();
            $table->date('submission_deadline')->nullable();
            $table->string('payment_method')->nullable();
            $table->decimal('grand_total', 12, 2)->nullable();
            $table->string('status', 50)->default('draft');

            $table->string('vendor_rep_name')->nullable();
            $table->string('vendor_rep_signature')->nullable();
            $table->date('vendor_rep_signed_at')->nullable();
            $table->string('vendor_company_stamp')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('rfq_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_id')->constrained('rfqs')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('item', 100)->nullable();
            $table->text('description')->nullable();
            $table->decimal('quantity', 12, 3)->default(1);
            $table->string('unit', 50)->nullable();
            $table->string('request_lead_time')->nullable();
            $table->string('compliance')->nullable();
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->decimal('line_total', 12, 2)->default(0);
            $table->string('quote_lead_time')->nullable();
            $table->string('warranty')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfq_items');
        Schema::dropIfExists('rfqs');
    }
};
