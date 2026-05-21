<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            $table->dropColumn('supporting_documents');
            $table->string('supporting_document_path', 500)->nullable()->after('classification');
            $table->string('supporting_document_name', 255)->nullable()->after('supporting_document_path');
        });
    }

    public function down(): void
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            $table->dropColumn(['supporting_document_path', 'supporting_document_name']);
            $table->text('supporting_documents')->nullable();
        });
    }
};
