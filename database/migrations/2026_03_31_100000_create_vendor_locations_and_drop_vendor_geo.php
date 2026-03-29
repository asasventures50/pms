<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Multiple branch locations per vendor; legacy single country/city/address moved into one primary row.
     */
    public function up(): void
    {
        Schema::create('vendor_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->text('address')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('whatsapp', 50)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('vendor_id');
            $table->index(['vendor_id', 'is_primary']);
        });

        if (Schema::hasColumn('vendors', 'country_id')) {
            DB::table('vendors')->orderBy('id')->chunk(100, function ($rows) {
                foreach ($rows as $row) {
                    $hasGeo = $row->country_id || $row->city_id
                        || ($row->address !== null && trim((string) $row->address) !== '');

                    if (! $hasGeo) {
                        continue;
                    }

                    DB::table('vendor_locations')->insert([
                        'vendor_id' => $row->id,
                        'country_id' => $row->country_id,
                        'city_id' => $row->city_id,
                        'address' => $row->address,
                        'phone' => null,
                        'whatsapp' => null,
                        'is_primary' => true,
                        'notes' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });

            $driver = Schema::getConnection()->getDriverName();
            // SQLite cannot reliably drop these FK-backed columns via ALTER; production uses MySQL.
            if ($driver !== 'sqlite') {
                Schema::table('vendors', function (Blueprint $table) {
                    $table->dropForeign(['country_id']);
                    $table->dropForeign(['city_id']);
                    $table->dropColumn(['country_id', 'city_id', 'address']);
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite' && ! Schema::hasColumn('vendors', 'country_id')) {
            Schema::table('vendors', function (Blueprint $table) {
                $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
                $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
                $table->text('address')->nullable();
            });
        }

        $primaryLocations = DB::table('vendor_locations')
            ->where('is_primary', true)
            ->orderBy('id')
            ->get()
            ->unique('vendor_id');

        foreach ($primaryLocations as $loc) {
            DB::table('vendors')->where('id', $loc->vendor_id)->update([
                'country_id' => $loc->country_id,
                'city_id' => $loc->city_id,
                'address' => $loc->address,
            ]);
        }

        Schema::dropIfExists('vendor_locations');
    }
};
