<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procurement_request_items', function (Blueprint $table) {
            $table->date('required_delivery_date')->nullable()->after('justification');
            $table->boolean('flexible_delivery_date')->default(true)->after('required_delivery_date');
            $table->string('delivery_location')->nullable()->after('flexible_delivery_date');
        });

        if (Schema::hasColumn('procurement_requests', 'required_delivery_date')) {
            $requests = DB::table('procurement_requests')
                ->select('id', 'required_delivery_date', 'flexible_delivery_date', 'delivery_location')
                ->get();

            foreach ($requests as $request) {
                DB::table('procurement_request_items')
                    ->where('procurement_request_id', $request->id)
                    ->update([
                        'required_delivery_date' => $request->required_delivery_date,
                        'flexible_delivery_date' => $request->flexible_delivery_date ?? true,
                        'delivery_location' => $request->delivery_location,
                    ]);
            }

            Schema::table('procurement_requests', function (Blueprint $table) {
                $table->dropColumn([
                    'required_delivery_date',
                    'flexible_delivery_date',
                    'delivery_location',
                ]);
            });
        }
    }

    public function down(): void
    {
        Schema::table('procurement_requests', function (Blueprint $table) {
            $table->date('required_delivery_date')->nullable();
            $table->string('delivery_location')->nullable();
            $table->boolean('flexible_delivery_date')->default(true)->after('delivery_location');
        });

        $requests = DB::table('procurement_requests')->pluck('id');

        foreach ($requests as $requestId) {
            $item = DB::table('procurement_request_items')
                ->where('procurement_request_id', $requestId)
                ->orderBy('sort_order')
                ->first();

            if ($item === null) {
                continue;
            }

            DB::table('procurement_requests')
                ->where('id', $requestId)
                ->update([
                    'required_delivery_date' => $item->required_delivery_date,
                    'flexible_delivery_date' => $item->flexible_delivery_date ?? true,
                    'delivery_location' => $item->delivery_location,
                ]);
        }

        Schema::table('procurement_request_items', function (Blueprint $table) {
            $table->dropColumn([
                'required_delivery_date',
                'flexible_delivery_date',
                'delivery_location',
            ]);
        });
    }
};
