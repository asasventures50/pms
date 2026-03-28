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

        Schema::create('categories', function (Blueprint $table) use ($isMysql) {
            if ($isMysql) {
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
            }

            $table->id();

            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            $table->string('status')->default('active');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
