<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Store RFQ channels as JSON array. Legacy single string (including "mixed") is converted.
     *
     * "mixed" is expanded to all four allowed methods so no information is lost.
     */
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->json('rfq_method_new')->nullable();
        });

        $allowed = ['email', 'portal', 'whatsapp', 'phone'];

        DB::table('vendors')->orderBy('id')->chunk(200, function ($rows) use ($allowed) {
            foreach ($rows as $row) {
                $raw = $row->rfq_method ?? null;
                $value = null;

                if ($raw === null || $raw === '') {
                    $value = null;
                } elseif ($raw === 'mixed') {
                    $value = $allowed;
                } elseif (in_array($raw, $allowed, true)) {
                    $value = [$raw];
                }

                DB::table('vendors')->where('id', $row->id)->update([
                    'rfq_method_new' => $value,
                ]);
            }
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('rfq_method');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->renameColumn('rfq_method_new', 'rfq_method');
        });
    }

    /**
     * Best-effort rollback: first value only, or "mixed" when multiple were stored.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('rfq_method_legacy')->nullable();
        });

        DB::table('vendors')->orderBy('id')->chunk(200, function ($rows) {
            foreach ($rows as $row) {
                $raw = $row->rfq_method;
                $str = null;

                if ($raw !== null && $raw !== '') {
                    $arr = is_string($raw) ? json_decode($raw, true) : $raw;
                    if (is_array($arr) && $arr !== []) {
                        $str = count($arr) > 1 ? 'mixed' : (string) reset($arr);
                    }
                }

                DB::table('vendors')->where('id', $row->id)->update(['rfq_method_legacy' => $str]);
            }
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('rfq_method');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->renameColumn('rfq_method_legacy', 'rfq_method');
        });
    }
};
