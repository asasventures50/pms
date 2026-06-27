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

        Schema::create('schedule_of_works', function (Blueprint $table) use ($isMysql) {
            if ($isMysql) {
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
            }

            $table->id();
            $table->string('document_number', 100)->unique();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('recipient_name', 255);
            $table->string('project_manager_name', 255)->nullable();
            $table->date('documented_at');
            $table->string('po_reference', 500)->nullable();
            $table->string('vendor_company_name', 255)->nullable();
            $table->string('currency_code', 3)->nullable();
            $table->json('scope_types')->nullable();
            $table->string('print_locale', 2)->default('ar');
            $table->decimal('total_price', 12, 2)->default(0);
            $table->json('notes')->nullable();
            $table->json('custom_fees')->nullable();
            $table->timestamps();
        });

        Schema::create('schedule_of_work_items', function (Blueprint $table) use ($isMysql) {
            if ($isMysql) {
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
            }

            $table->id();
            $table->foreignId('schedule_of_work_id')->constrained('schedule_of_works')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->unsignedSmallInteger('line_number');
            $table->string('project_zone', 255)->nullable();
            $table->text('description');
            $table->decimal('quantity', 12, 3)->default(0);
            $table->string('unit', 50)->nullable();
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_of_work_items');
        Schema::dropIfExists('schedule_of_works');
    }
};
