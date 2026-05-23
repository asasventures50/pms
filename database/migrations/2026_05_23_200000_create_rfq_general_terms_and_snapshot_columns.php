<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfq_general_terms', function (Blueprint $table) {
            $table->id();
            $table->text('body');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('rfqs', function (Blueprint $table) {
            $table->json('terms')->nullable()->after('status');
        });

        Schema::table('rfq_items', function (Blueprint $table) {
            $table->json('line_terms')->nullable()->after('warranty');
        });
    }

    public function down(): void
    {
        Schema::table('rfq_items', function (Blueprint $table) {
            $table->dropColumn('line_terms');
        });

        Schema::table('rfqs', function (Blueprint $table) {
            $table->dropColumn('terms');
        });

        Schema::dropIfExists('rfq_general_terms');
    }
};
