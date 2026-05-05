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

        Schema::create('purchase_orders', function (Blueprint $table) use ($isMysql) {
            if ($isMysql) {
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
            }

            $table->id();

            $table->string('po_number', 100)->unique();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->text('notes')->nullable();

            // Vendor (single)
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();

            // Price
            $table->decimal('total_price', 12, 2)->nullable();

            // Statuses
            $table->string('status', 50)->default('draft');           // draft, ordered, shipped, delivered, cancelled
            $table->string('payment_status', 50)->default('unpaid');  // unpaid, partial, paid

            // Dates
            $table->date('ordered_at')->nullable();
            $table->date('delivered_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
