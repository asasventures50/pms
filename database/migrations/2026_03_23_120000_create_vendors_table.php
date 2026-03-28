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

        Schema::create('vendors', function (Blueprint $table) use ($isMysql) {
            if ($isMysql) {
                $table->charset = 'utf8mb4';
                $table->collation = 'utf8mb4_unicode_ci';
            }

            $table->id();

            $table->string('vendor_code')->unique();

            $table->string('name');
            $table->string('language', 2);
            $table->text('description')->nullable();
            $table->text('notes')->nullable();

            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->text('address')->nullable();

            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();

            $table->string('primary_contact_name')->nullable();
            $table->string('primary_contact_position')->nullable();
            $table->string('primary_contact_phone')->nullable();
            $table->string('primary_contact_email')->nullable();

            $table->string('rfq_method')->nullable();
            $table->string('pricing_frequency')->nullable();
            $table->unsignedInteger('delivery_lead_time_days')->nullable();
            $table->unsignedInteger('execution_lead_time_days')->nullable();

            $table->string('payment_method')->nullable();
            $table->text('payment_terms')->nullable();
            $table->text('commercial_terms')->nullable();
            $table->text('technical_capabilities')->nullable();

            $table->unsignedInteger('bulletin_price_validity_days')->nullable();
            $table->string('currency_code', 3)->nullable();

            $table->string('company_type')->nullable();
            $table->string('status');
            $table->string('coverage_type')->nullable();

            $table->string('tax_number')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('license_number')->nullable();

            $table->boolean('is_brochure_available')->default(false);
            $table->decimal('rating', 3, 2)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('language');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
