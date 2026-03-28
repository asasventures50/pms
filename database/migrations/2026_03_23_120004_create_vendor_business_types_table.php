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

        Schema::create('vendor_business_types', function (Blueprint $table) use ($isMysql) {
            if ($isMysql) {
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
            }

            $table->id();

            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->string('business_type');

            $table->timestamps();

            $table->unique(['vendor_id', 'business_type'], 'vendor_business_type_unique');
            $table->index('vendor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_business_types');
    }
};
