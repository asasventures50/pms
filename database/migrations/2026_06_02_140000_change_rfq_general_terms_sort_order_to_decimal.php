<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rfq_general_terms', function (Blueprint $table) {
            $table->decimal('sort_order', 10, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('rfq_general_terms', function (Blueprint $table) {
            $table->unsignedSmallInteger('sort_order')->default(0)->change();
        });
    }
};
