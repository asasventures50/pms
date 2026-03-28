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

        Schema::create('vendor_brochures', function (Blueprint $table) use ($isMysql) {
            if ($isMysql) {
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
            }

            $table->id();

            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();

            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('vendor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_brochures');
    }
};
