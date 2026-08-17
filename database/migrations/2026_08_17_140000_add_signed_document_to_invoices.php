<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('signed_document_path', 500)->nullable()->after('notes');
            $table->string('signed_document_original_name', 255)->nullable()->after('signed_document_path');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['signed_document_path', 'signed_document_original_name']);
        });
    }
};
