<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rfq_items', function (Blueprint $table) {
            $table->foreignId('procurement_request_item_id')
                ->nullable()
                ->after('rfq_id')
                ->constrained('procurement_request_items')
                ->nullOnDelete();

            $table->unique('procurement_request_item_id', 'rfq_items_pr_item_unique');
        });
    }

    public function down(): void
    {
        Schema::table('rfq_items', function (Blueprint $table) {
            $table->dropUnique('rfq_items_pr_item_unique');
            $table->dropConstrainedForeignId('procurement_request_item_id');
        });
    }
};
