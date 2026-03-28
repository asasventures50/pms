<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $isMysql = DB::getDriverName() === 'mysql';

        Schema::create('vendor_categories', function (Blueprint $table) use ($isMysql) {
            if ($isMysql) {
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
            }

            $table->id();

            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('subcategory_id')->nullable()->constrained('subcategories')->nullOnDelete();

            $table->boolean('is_primary')->default(false);

            $table->timestamps();

            $table->unique(['vendor_id', 'category_id', 'subcategory_id'], 'vendor_category_unique');

            $table->index('vendor_id');
            $table->index('category_id');
            $table->index('subcategory_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_categories');
    }
};
