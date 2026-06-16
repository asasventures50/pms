<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rfqs', function (Blueprint $table) {
            $table->foreignId('selected_vendor_quotation_id')
                ->nullable()
                ->after('status')
                ->constrained('vendor_quotations')
                ->nullOnDelete();
            $table->foreignId('selected_by')
                ->nullable()
                ->after('selected_vendor_quotation_id')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('selected_at')->nullable()->after('selected_by');
        });
    }

    public function down(): void
    {
        Schema::table('rfqs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('selected_vendor_quotation_id');
            $table->dropConstrainedForeignId('selected_by');
            $table->dropColumn('selected_at');
        });
    }
};
