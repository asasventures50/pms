<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            if (! Schema::hasColumn('countries', 'name_ar')) {
                $table->string('name_ar')->nullable()->after('name');
            }
            if (! Schema::hasColumn('countries', 'name_en')) {
                $table->string('name_en')->nullable()->after('name_ar');
            }
            if (! Schema::hasColumn('countries', 'status')) {
                $table->string('status', 20)->default('active')->after('flag_emoji');
            }
            if (! Schema::hasColumn('countries', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        DB::table('countries')
            ->whereNull('name_ar')
            ->update(['name_ar' => DB::raw('name')]);
        DB::table('countries')
            ->whereNull('name_en')
            ->update(['name_en' => DB::raw('name')]);

        Schema::table('cities', function (Blueprint $table) {
            if (! Schema::hasColumn('cities', 'name_ar')) {
                $table->string('name_ar')->nullable()->after('name');
            }
            if (! Schema::hasColumn('cities', 'name_en')) {
                $table->string('name_en')->nullable()->after('name_ar');
            }
            if (! Schema::hasColumn('cities', 'status')) {
                $table->string('status', 20)->default('active')->after('name_en');
            }
            if (! Schema::hasColumn('cities', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        DB::table('cities')
            ->whereNull('name_ar')
            ->update(['name_ar' => DB::raw('name')]);
        DB::table('cities')
            ->whereNull('name_en')
            ->update(['name_en' => DB::raw('name')]);

        Schema::table('countries', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->index('status');
            $table->unique(['country_id', 'name_ar', 'deleted_at'], 'cities_country_name_ar_deleted_unique');
            $table->unique(['country_id', 'name_en', 'deleted_at'], 'cities_country_name_en_deleted_unique');
        });
    }

    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropUnique('cities_country_name_ar_deleted_unique');
            $table->dropUnique('cities_country_name_en_deleted_unique');
            $table->dropIndex(['status']);
            if (Schema::hasColumn('cities', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
            if (Schema::hasColumn('cities', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('cities', 'name_en')) {
                $table->dropColumn('name_en');
            }
            if (Schema::hasColumn('cities', 'name_ar')) {
                $table->dropColumn('name_ar');
            }
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->dropIndex(['status']);
            if (Schema::hasColumn('countries', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
            if (Schema::hasColumn('countries', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('countries', 'name_en')) {
                $table->dropColumn('name_en');
            }
            if (Schema::hasColumn('countries', 'name_ar')) {
                $table->dropColumn('name_ar');
            }
        });
    }
};
