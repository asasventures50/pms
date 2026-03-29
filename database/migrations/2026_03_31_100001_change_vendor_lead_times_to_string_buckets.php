<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Replace numeric lead time days with structured string values.
     */
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('delivery_lead_time', 40)->nullable();
            $table->string('execution_lead_time', 40)->nullable();
        });

        $map = function (?int $days): ?string {
            if ($days === null) {
                return null;
            }
            if ($days <= 7) {
                return 'up_to_1_week';
            }
            if ($days <= 14) {
                return 'one_to_two_weeks';
            }
            if ($days <= 31) {
                return 'one_month';
            }

            return 'more_than_one_month';
        };

        if (Schema::hasColumn('vendors', 'delivery_lead_time_days')) {
            DB::table('vendors')->orderBy('id')->chunk(200, function ($rows) use ($map) {
                foreach ($rows as $row) {
                    DB::table('vendors')->where('id', $row->id)->update([
                        'delivery_lead_time' => $map(isset($row->delivery_lead_time_days) ? (int) $row->delivery_lead_time_days : null),
                        'execution_lead_time' => $map(isset($row->execution_lead_time_days) ? (int) $row->execution_lead_time_days : null),
                    ]);
                }
            });

            Schema::table('vendors', function (Blueprint $table) {
                $table->dropColumn(['delivery_lead_time_days', 'execution_lead_time_days']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->unsignedInteger('delivery_lead_time_days')->nullable();
            $table->unsignedInteger('execution_lead_time_days')->nullable();
        });

        $toDays = function (?string $v): ?int {
            return match ($v) {
                'up_to_1_week' => 7,
                'one_to_two_weeks' => 14,
                'one_month' => 30,
                'more_than_one_month' => 60,
                default => null,
            };
        };

        DB::table('vendors')->orderBy('id')->chunk(200, function ($rows) use ($toDays) {
            foreach ($rows as $row) {
                DB::table('vendors')->where('id', $row->id)->update([
                    'delivery_lead_time_days' => $toDays($row->delivery_lead_time ?? null),
                    'execution_lead_time_days' => $toDays($row->execution_lead_time ?? null),
                ]);
            }
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['delivery_lead_time', 'execution_lead_time']);
        });
    }
};
