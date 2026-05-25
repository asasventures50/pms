<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procurement_request_items', function (Blueprint $table) {
            $table->longText('scope_of_work')->nullable()->after('justification');
        });
    }

    public function down(): void
    {
        Schema::table('procurement_request_items', function (Blueprint $table) {
            $table->dropColumn('scope_of_work');
        });
    }
};
