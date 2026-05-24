<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rfq_general_terms', function (Blueprint $table) {
            $table->text('body_ar')->nullable()->after('scope_type');
            $table->text('body_en')->nullable()->after('body_ar');
        });

        foreach (DB::table('rfq_general_terms')->select('id', 'body')->get() as $row) {
            DB::table('rfq_general_terms')->where('id', $row->id)->update([
                'body_en' => $row->body,
                'body_ar' => null,
            ]);
        }

        Schema::table('rfq_general_terms', function (Blueprint $table) {
            $table->dropColumn('body');
        });

        Schema::table('rfqs', function (Blueprint $table) {
            $table->string('terms_locale', 2)->default('en')->after('terms');
        });
    }

    public function down(): void
    {
        Schema::table('rfq_general_terms', function (Blueprint $table) {
            $table->text('body')->nullable()->after('scope_type');
        });

        foreach (DB::table('rfq_general_terms')->select('id', 'body_en', 'body_ar')->get() as $row) {
            DB::table('rfq_general_terms')->where('id', $row->id)->update([
                'body' => $row->body_en ?: $row->body_ar,
            ]);
        }

        Schema::table('rfq_general_terms', function (Blueprint $table) {
            $table->dropColumn(['body_ar', 'body_en']);
        });

        Schema::table('rfqs', function (Blueprint $table) {
            $table->dropColumn('terms_locale');
        });
    }
};
