<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procurement_request_documents', function (Blueprint $table) {
            if (! Schema::hasColumn('procurement_request_documents', 'procurement_request_item_id')) {
                $table->unsignedBigInteger('procurement_request_item_id')->nullable()->after('id');
            }
        });

        Schema::table('procurement_request_documents', function (Blueprint $table) {
            $table->foreign('procurement_request_item_id', 'pr_req_doc_item_fk')
                ->references('id')
                ->on('procurement_request_items')
                ->cascadeOnDelete();
        });

        $documents = DB::table('procurement_request_documents')->get(['id', 'procurement_request_id']);

        foreach ($documents as $document) {
            $itemId = DB::table('procurement_request_items')
                ->where('procurement_request_id', $document->procurement_request_id)
                ->orderBy('sort_order')
                ->value('id');

            if ($itemId === null) {
                DB::table('procurement_request_documents')->where('id', $document->id)->delete();

                continue;
            }

            DB::table('procurement_request_documents')
                ->where('id', $document->id)
                ->update(['procurement_request_item_id' => $itemId]);
        }

        if (Schema::hasColumn('procurement_request_documents', 'procurement_request_id')) {
            Schema::table('procurement_request_documents', function (Blueprint $table) {
                $table->dropForeign(['procurement_request_id']);
                $table->dropColumn('procurement_request_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('procurement_request_documents', function (Blueprint $table) {
            if (! Schema::hasColumn('procurement_request_documents', 'procurement_request_id')) {
                $table->unsignedBigInteger('procurement_request_id')->nullable()->after('id');
            }
        });

        Schema::table('procurement_request_documents', function (Blueprint $table) {
            $table->foreign('procurement_request_id', 'pr_req_doc_request_fk')
                ->references('id')
                ->on('procurement_requests')
                ->cascadeOnDelete();
        });

        $documents = DB::table('procurement_request_documents')->get(['id', 'procurement_request_item_id']);

        foreach ($documents as $document) {
            $requestId = DB::table('procurement_request_items')
                ->where('id', $document->procurement_request_item_id)
                ->value('procurement_request_id');

            if ($requestId === null) {
                DB::table('procurement_request_documents')->where('id', $document->id)->delete();

                continue;
            }

            DB::table('procurement_request_documents')
                ->where('id', $document->id)
                ->update(['procurement_request_id' => $requestId]);
        }

        Schema::table('procurement_request_documents', function (Blueprint $table) {
            $table->dropForeign('pr_req_doc_item_fk');
            $table->dropColumn('procurement_request_item_id');
        });
    }
};
