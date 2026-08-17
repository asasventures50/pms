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

        Schema::create('purchase_order_payment_terms', function (Blueprint $table) use ($isMysql) {
            if ($isMysql) {
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
            }

            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->text('milestone');
            $table->decimal('percentage', 5, 2)->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['purchase_order_id', 'sort_order']);
        });

        $this->backfillLegacyPaymentTerms();
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_payment_terms');
    }

    /**
     * Preserve existing free-text payment terms as a single milestone row.
     * Does not drop or rewrite purchase_orders.payment_terms.
     */
    private function backfillLegacyPaymentTerms(): void
    {
        $orders = DB::table('purchase_orders')
            ->select('id', 'payment_terms')
            ->whereNotNull('payment_terms')
            ->where('payment_terms', '!=', '')
            ->orderBy('id')
            ->get();

        $now = now();

        foreach ($orders as $order) {
            $text = trim((string) $order->payment_terms);
            if ($text === '') {
                continue;
            }

            $exists = DB::table('purchase_order_payment_terms')
                ->where('purchase_order_id', $order->id)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('purchase_order_payment_terms')->insert([
                'purchase_order_id' => $order->id,
                'milestone' => $text,
                'percentage' => null,
                'amount' => null,
                'invoice_id' => null,
                'sort_order' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
};
