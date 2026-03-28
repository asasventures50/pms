<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('id');
            $table->string('name_ar')->nullable()->after('name_en');
        });

        foreach (DB::table('categories')->whereNotNull('name')->cursor() as $row) {
            DB::table('categories')->where('id', $row->id)->update([
                'name_en' => $row->name,
                'name_ar' => $row->name,
            ]);
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->dropColumn('name');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->string('name_en')->nullable(false)->change();
            $table->string('name_ar')->nullable(false)->change();
            $table->unique('name_en');
        });

        Schema::table('subcategories', function (Blueprint $table) {
            $table->string('name_en')->nullable()->after('category_id');
            $table->string('name_ar')->nullable()->after('name_en');
        });

        foreach (DB::table('subcategories')->whereNotNull('name')->cursor() as $row) {
            DB::table('subcategories')->where('id', $row->id)->update([
                'name_en' => $row->name,
                'name_ar' => $row->name,
            ]);
        }

        Schema::table('subcategories', function (Blueprint $table) {
            $table->dropUnique(['category_id', 'name']);
            $table->dropColumn('name');
        });

        Schema::table('subcategories', function (Blueprint $table) {
            $table->string('name_en')->nullable(false)->change();
            $table->string('name_ar')->nullable(false)->change();
            $table->unique(['category_id', 'name_en']);
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['name_en']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->string('name')->after('id');
        });

        foreach (DB::table('categories')->cursor() as $row) {
            DB::table('categories')->where('id', $row->id)->update([
                'name' => $row->name_en,
            ]);
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['name_en', 'name_ar']);
            $table->unique('name');
        });

        Schema::table('subcategories', function (Blueprint $table) {
            $table->dropUnique(['category_id', 'name_en']);
        });

        Schema::table('subcategories', function (Blueprint $table) {
            $table->string('name')->after('category_id');
        });

        foreach (DB::table('subcategories')->cursor() as $row) {
            DB::table('subcategories')->where('id', $row->id)->update([
                'name' => $row->name_en,
            ]);
        }

        Schema::table('subcategories', function (Blueprint $table) {
            $table->dropColumn(['name_en', 'name_ar']);
            $table->unique(['category_id', 'name']);
        });
    }
};
