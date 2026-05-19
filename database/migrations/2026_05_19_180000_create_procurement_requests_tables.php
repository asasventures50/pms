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

        Schema::create('procurement_requests', function (Blueprint $table) use ($isMysql) {
            if ($isMysql) {
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
            }

            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('request_number', 100)->unique();

            $table->string('requestor_name')->nullable();
            $table->date('requested_at')->nullable();
            $table->string('requestor_department')->nullable();

            $table->date('required_delivery_date')->nullable();
            $table->string('delivery_location')->nullable();

            $table->text('classification')->nullable();
            $table->text('supporting_documents')->nullable();

            $table->string('received_by')->nullable();
            $table->text('procurement_note')->nullable();

            $table->string('status', 50)->default('draft');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('procurement_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_request_id')->constrained('procurement_requests')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('zone', 100)->nullable();
            $table->string('category', 255)->nullable();
            $table->string('subcategory', 255)->nullable();
            $table->string('scope_type', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('unit', 50)->nullable();
            $table->decimal('quantity', 12, 3)->default(1);
            $table->text('justification')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_request_items');
        Schema::dropIfExists('procurement_requests');
    }
};
