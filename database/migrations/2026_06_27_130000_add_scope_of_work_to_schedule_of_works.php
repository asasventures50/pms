<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedule_of_works', function (Blueprint $table) {
            $table->text('scope_of_work')->nullable()->after('scope_types');
        });
    }

    public function down(): void
    {
        Schema::table('schedule_of_works', function (Blueprint $table) {
            $table->dropColumn('scope_of_work');
        });
    }
};
