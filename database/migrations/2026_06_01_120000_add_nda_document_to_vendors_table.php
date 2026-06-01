<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('nda_file_name')->nullable()->after('license_number');
            $table->string('nda_file_path')->nullable()->after('nda_file_name');
            $table->string('nda_file_type')->nullable()->after('nda_file_path');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['nda_file_name', 'nda_file_path', 'nda_file_type']);
        });
    }
};
