<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            if (Schema::hasColumn('vendors', 'country')) {
                $table->dropColumn(['country', 'city']);
            }

            $table->foreignId('country_id')->nullable()->after('notes')->constrained('countries')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->after('country_id')->constrained('cities')->nullOnDelete();

            $table->string('secondary_contact_name')->nullable()->after('primary_contact_email');
            $table->string('secondary_contact_position')->nullable()->after('secondary_contact_name');
            $table->string('secondary_contact_phone')->nullable()->after('secondary_contact_position');
            $table->string('secondary_contact_email')->nullable()->after('secondary_contact_phone');

            $table->index('country_id');
            $table->index('city_id');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropForeign(['city_id']);
            $table->dropColumn([
                'country_id',
                'city_id',
                'secondary_contact_name',
                'secondary_contact_position',
                'secondary_contact_phone',
                'secondary_contact_email',
            ]);

            $table->string('country')->nullable()->after('notes');
            $table->string('city')->nullable()->after('country');
        });
    }
};
