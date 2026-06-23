<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->json('notes')->nullable()->after('merged_lines');
            $table->string('project_manager_name', 255)->nullable()->after('recipient_name');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->string('project_zone', 255)->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn('project_zone');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['notes', 'project_manager_name']);
        });
    }
};
