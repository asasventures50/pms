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

        Schema::create('subcategories', function (Blueprint $table) use ($isMysql) {
            if ($isMysql) {
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
            }

            $table->id();

            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();

            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();

            $table->string('status')->default('active');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['category_id', 'name']);
            $table->unique(['category_id', 'slug']);

            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subcategories');
    }
};
