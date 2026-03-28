<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('vendors')->whereNotNull('rating')->cursor() as $row) {
            $rounded = max(1, min(5, (int) round((float) $row->rating)));
            DB::table('vendors')->where('id', $row->id)->update(['rating' => $rounded]);
        }

        Schema::table('vendors', function (Blueprint $table) {
            $table->unsignedTinyInteger('rating')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->decimal('rating', 3, 2)->nullable()->change();
        });
    }
};
