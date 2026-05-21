<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procurement_request_items', function (Blueprint $table) {
            $table->string('line_number', 20)->nullable()->after('sort_order');
            $table->string('project', 255)->nullable()->after('line_number');
        });
    }

    public function down(): void
    {
        Schema::table('procurement_request_items', function (Blueprint $table) {
            $table->dropColumn(['line_number', 'project']);
        });
    }
};
