<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procurement_request_items', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('line_number')->constrained('projects')->nullOnDelete();
            $table->foreignId('zone_id')->nullable()->after('project_id')->constrained('zones')->nullOnDelete();
        });

        Schema::table('procurement_request_items', function (Blueprint $table) {
            if (Schema::hasColumn('procurement_request_items', 'project')) {
                $table->dropColumn('project');
            }
            if (Schema::hasColumn('procurement_request_items', 'zone')) {
                $table->dropColumn('zone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('procurement_request_items', function (Blueprint $table) {
            $table->string('project', 255)->nullable()->after('line_number');
            $table->string('zone', 100)->nullable()->after('project');
        });

        Schema::table('procurement_request_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('zone_id');
            $table->dropConstrainedForeignId('project_id');
        });
    }
};
