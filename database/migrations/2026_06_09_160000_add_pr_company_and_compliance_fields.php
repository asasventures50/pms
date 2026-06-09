<?php

use App\Enums\Procurement\PrCompany;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('procurement_requests')) {
            return;
        }

        Schema::table('procurement_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('procurement_requests', 'company_key')) {
                $table->string('company_key', 50)->nullable()->after('requestor_department');
            }

            if (! Schema::hasColumn('procurement_requests', 'after_sale_service_applicable')) {
                $table->boolean('after_sale_service_applicable')->nullable()->after('nda_required');
            }

            if (! Schema::hasColumn('procurement_requests', 'compliance_verification_required')) {
                $table->boolean('compliance_verification_required')->nullable()->after('after_sale_service_applicable');
            }

            if (! Schema::hasColumn('procurement_requests', 'compliance_prequalification_required')) {
                $table->boolean('compliance_prequalification_required')->nullable()->after('compliance_verification_required');
            }

            if (! Schema::hasColumn('procurement_requests', 'conflict_of_interest_required')) {
                $table->boolean('conflict_of_interest_required')->nullable()->after('compliance_prequalification_required');
            }

            if (! Schema::hasColumn('procurement_requests', 'commitment_compliance_required')) {
                $table->boolean('commitment_compliance_required')->nullable()->after('conflict_of_interest_required');
            }
        });

        DB::table('procurement_requests')
            ->whereNull('company_key')
            ->update(['company_key' => PrCompany::AsasVentures->value]);

        if (Schema::hasTable('procurement_request_items')) {
            DB::table('procurement_requests')
                ->select(['id', 'scope_of_work'])
                ->orderBy('id')
                ->chunkById(100, function ($rows): void {
                    foreach ($rows as $row) {
                        if (trim((string) ($row->scope_of_work ?? '')) !== '') {
                            continue;
                        }

                        $scope = DB::table('procurement_request_items')
                            ->where('procurement_request_id', $row->id)
                            ->whereNotNull('scope_of_work')
                            ->where('scope_of_work', '!=', '')
                            ->orderBy('sort_order')
                            ->value('scope_of_work');

                        if ($scope !== null) {
                            DB::table('procurement_requests')
                                ->where('id', $row->id)
                                ->update(['scope_of_work' => $scope]);
                        }
                    }
                });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('procurement_requests')) {
            return;
        }

        Schema::table('procurement_requests', function (Blueprint $table) {
            foreach ([
                'company_key',
                'after_sale_service_applicable',
                'compliance_verification_required',
                'compliance_prequalification_required',
                'conflict_of_interest_required',
                'commitment_compliance_required',
            ] as $column) {
                if (Schema::hasColumn('procurement_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
