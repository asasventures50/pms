<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedule_of_works', function (Blueprint $table) {
            $table->foreignId('procurement_request_id')
                ->nullable()
                ->after('vendor_id')
                ->constrained('procurement_requests')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('schedule_of_works', function (Blueprint $table) {
            $table->dropConstrainedForeignId('procurement_request_id');
        });
    }
};
