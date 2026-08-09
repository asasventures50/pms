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

        Schema::create('rfq_vendor_quotation_invites', function (Blueprint $table) use ($isMysql) {
            if ($isMysql) {
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
            }

            $table->id();
            $table->foreignId('rfq_id')->constrained('rfqs')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->string('ui_locale', 20);
            $table->boolean('include_terms')->default(false);
            $table->string('status', 20)->default('pending');
            $table->foreignId('vendor_quotation_id')->nullable()->constrained('vendor_quotations')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->index(['rfq_id', 'vendor_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfq_vendor_quotation_invites');
    }
};
