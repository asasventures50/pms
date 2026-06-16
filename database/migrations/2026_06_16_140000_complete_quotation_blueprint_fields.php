<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rfqs', function (Blueprint $table) {
            $table->unsignedSmallInteger('revision_number')->default(0)->after('rfq_number');
            $table->dateTime('submission_deadline_at')->nullable()->after('submission_deadline');
            $table->string('submission_timezone', 64)->nullable()->after('submission_deadline_at');
        });

        Schema::table('vendor_quotations', function (Blueprint $table) {
            $table->string('delivery_terms')->nullable()->after('payment_method');
            $table->string('vendor_rep_signature_path')->nullable()->after('vendor_rep_signature');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_quotations', function (Blueprint $table) {
            $table->dropColumn(['delivery_terms', 'vendor_rep_signature_path']);
        });

        Schema::table('rfqs', function (Blueprint $table) {
            $table->dropColumn(['revision_number', 'submission_deadline_at', 'submission_timezone']);
        });
    }
};
