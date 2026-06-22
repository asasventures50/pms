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

        Schema::table('invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('invoices', 'transport_fees')) {
                $table->decimal('transport_fees', 12, 2)->default(0)->after('currency_code');
            }
            if (! Schema::hasColumn('invoices', 'supervision_fees')) {
                $table->decimal('supervision_fees', 12, 2)->default(0)->after('transport_fees');
            }
            if (! Schema::hasColumn('invoices', 'administrative_fees')) {
                $table->decimal('administrative_fees', 12, 2)->default(0)->after('supervision_fees');
            }
            if (! Schema::hasColumn('invoices', 'logistics_fees')) {
                $table->decimal('logistics_fees', 12, 2)->default(0)->after('administrative_fees');
            }
        });

        if ($isMysql) {
            DB::statement('ALTER TABLE invoices MODIFY po_number VARCHAR(500) NOT NULL');
        }

        if (! Schema::hasTable('invoice_purchase_orders')) {
            Schema::create('invoice_purchase_orders', function (Blueprint $table) use ($isMysql) {
                if ($isMysql) {
                    $table->charset = 'utf8mb4';
                    $table->collation = 'utf8mb4_unicode_ci';
                }

                $table->id();
                $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
                $table->foreignId('purchase_order_id')->constrained('purchase_orders')->restrictOnDelete();
                $table->timestamps();

                $table->unique(['invoice_id', 'purchase_order_id']);
            });
        }

        if (Schema::hasColumn('invoices', 'purchase_order_id')) {
            DB::table('invoices')
                ->whereNotNull('purchase_order_id')
                ->orderBy('id')
                ->chunkById(100, function ($rows) {
                    foreach ($rows as $row) {
                        DB::table('invoice_purchase_orders')->updateOrInsert(
                            [
                                'invoice_id' => $row->id,
                                'purchase_order_id' => $row->purchase_order_id,
                            ],
                            [
                                'created_at' => now(),
                                'updated_at' => now(),
                            ],
                        );
                    }
                });

            Schema::table('invoices', function (Blueprint $table) {
                $table->dropForeign(['purchase_order_id']);
                $table->dropColumn('purchase_order_id');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('invoices', 'purchase_order_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->foreignId('purchase_order_id')->nullable()->after('invoice_number')->constrained('purchase_orders')->restrictOnDelete();
            });

            if (Schema::hasTable('invoice_purchase_orders')) {
                DB::table('invoice_purchase_orders')
                    ->select('invoice_id', DB::raw('MIN(purchase_order_id) as purchase_order_id'))
                    ->groupBy('invoice_id')
                    ->orderBy('invoice_id')
                    ->each(function ($row) {
                        DB::table('invoices')
                            ->where('id', $row->invoice_id)
                            ->update(['purchase_order_id' => $row->purchase_order_id]);
                    });
            }
        }

        Schema::dropIfExists('invoice_purchase_orders');

        Schema::table('invoices', function (Blueprint $table) {
            foreach (['logistics_fees', 'administrative_fees', 'supervision_fees', 'transport_fees'] as $column) {
                if (Schema::hasColumn('invoices', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
