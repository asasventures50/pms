<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quick_receipts', function (Blueprint $table) {
            $table->dropColumn('category');
        });

        Schema::table('quick_receipts', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->after('expense_date')
                ->constrained('categories')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('quick_receipts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });

        Schema::table('quick_receipts', function (Blueprint $table) {
            $table->string('category', 255)->after('expense_date');
        });
    }
};
