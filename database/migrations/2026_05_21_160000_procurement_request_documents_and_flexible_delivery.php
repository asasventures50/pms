<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_request_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_request_id')->constrained('procurement_requests')->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path', 500);
            $table->timestamps();
        });

        if (Schema::hasColumn('procurement_requests', 'supporting_document_path')) {
            $rows = DB::table('procurement_requests')
                ->whereNotNull('supporting_document_path')
                ->where('supporting_document_path', '!=', '')
                ->get(['id', 'supporting_document_path', 'supporting_document_name']);

            foreach ($rows as $row) {
                DB::table('procurement_request_documents')->insert([
                    'procurement_request_id' => $row->id,
                    'file_name' => $row->supporting_document_name ?: basename($row->supporting_document_path),
                    'file_path' => $row->supporting_document_path,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            Schema::table('procurement_requests', function (Blueprint $table) {
                $table->dropColumn(['supporting_document_path', 'supporting_document_name']);
            });
        }

        Schema::table('procurement_requests', function (Blueprint $table) {
            if (Schema::hasColumn('procurement_requests', 'delivery_completed')) {
                $table->dropColumn('delivery_completed');
            }
            $table->boolean('flexible_delivery_date')->default(true)->after('delivery_location');
        });
    }

    public function down(): void
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            $table->dropColumn('flexible_delivery_date');
            $table->boolean('delivery_completed')->default(false);
            $table->string('supporting_document_path', 500)->nullable();
            $table->string('supporting_document_name', 255)->nullable();
        });

        Schema::dropIfExists('procurement_request_documents');
    }
};
