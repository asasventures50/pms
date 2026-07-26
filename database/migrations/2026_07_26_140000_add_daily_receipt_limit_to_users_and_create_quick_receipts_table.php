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

        Schema::table('users', function (Blueprint $table) {
            $table->decimal('daily_receipt_limit', 12, 2)->default(200)->after('currency_code');
        });

        Schema::create('quick_receipts', function (Blueprint $table) use ($isMysql) {
            if ($isMysql) {
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
            }

            $table->id();
            $table->string('code', 50)->unique();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency_code', 3);
            $table->date('expense_date');
            $table->string('category', 255);
            $table->string('provider_name', 255)->nullable();
            $table->string('attachment_path', 500)->nullable();
            $table->string('attachment_original_name', 255)->nullable();
            $table->string('status', 30)->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'expense_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quick_receipts');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('daily_receipt_limit');
        });
    }
};
