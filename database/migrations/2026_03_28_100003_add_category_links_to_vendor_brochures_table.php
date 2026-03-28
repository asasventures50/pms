<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_brochures', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('vendor_id')->constrained('categories')->nullOnDelete();
            $table->foreignId('subcategory_id')->nullable()->after('category_id')->constrained('subcategories')->nullOnDelete();

            $table->index('category_id');
            $table->index('subcategory_id');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_brochures', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['subcategory_id']);
            $table->dropColumn(['category_id', 'subcategory_id']);
        });
    }
};
