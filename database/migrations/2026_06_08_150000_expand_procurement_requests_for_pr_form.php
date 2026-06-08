<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createChildTables();

        if (! Schema::hasColumn('procurement_requests', 'project_id')) {
            Schema::table('procurement_requests', function (Blueprint $table) {
                $table->foreignId('project_id')->nullable()->after('requestor_department')->constrained('projects')->nullOnDelete();
                $table->foreignId('zone_id')->nullable()->after('project_id')->constrained('zones')->nullOnDelete();
                $table->foreignId('category_id')->nullable()->after('zone_id')->constrained('categories')->nullOnDelete();
                $table->foreignId('subcategory_id')->nullable()->after('category_id')->constrained('subcategories')->nullOnDelete();

                $table->json('procurement_types')->nullable()->after('subcategory_id');
                $table->json('geographic_scopes')->nullable()->after('procurement_types');
                $table->json('vendor_types')->nullable()->after('geographic_scopes');

                $table->text('justification')->nullable()->after('vendor_types');
                $table->unsignedSmallInteger('delivery_lead_time_days')->nullable()->after('justification');
                $table->string('delivery_location')->nullable()->after('delivery_lead_time_days');
                $table->boolean('flexible_delivery_date')->default(true)->after('delivery_location');

                $table->char('currency_code', 3)->nullable()->after('flexible_delivery_date');
                $table->boolean('samples_required')->nullable()->after('currency_code');
                $table->text('scope_of_work')->nullable()->after('samples_required');

                $table->boolean('nda_required')->nullable()->after('scope_of_work');
                $table->boolean('primary_insurance_applicable')->nullable()->after('nda_required');
                $table->boolean('final_insurance_applicable')->nullable()->after('primary_insurance_applicable');
                $table->decimal('warranty_years', 4, 1)->nullable()->after('final_insurance_applicable');
                $table->text('warranty_coverage')->nullable()->after('warranty_years');
            });
        }

        if (! Schema::hasColumn('procurement_request_items', 'item_name')) {
            Schema::table('procurement_request_items', function (Blueprint $table) {
                $table->string('item_name', 255)->nullable()->after('line_number');
                $table->decimal('unit_price', 15, 4)->nullable()->default(0)->after('quantity');
                $table->decimal('total_price', 15, 4)->nullable()->after('unit_price');
            });
        }

        if (! Schema::hasColumn('procurement_request_documents', 'procurement_request_id')) {
            Schema::table('procurement_request_documents', function (Blueprint $table) {
                $table->unsignedBigInteger('procurement_request_id')->nullable()->after('id');
                $table->foreign('procurement_request_id', 'pr_req_doc_request_fk')
                    ->references('id')->on('procurement_requests')->cascadeOnDelete();
                $table->string('document_type')->nullable()->after('procurement_request_item_id');
                $table->text('file_description')->nullable()->after('file_path');
            });
        }

        $this->migrateExistingProcurementRequests();
    }

    private function createChildTables(): void
    {
        if (! Schema::hasTable('procurement_request_payment_terms')) {
            Schema::create('procurement_request_payment_terms', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('procurement_request_id');
                $table->foreign('procurement_request_id', 'pr_payment_terms_request_fk')
                    ->references('id')->on('procurement_requests')->cascadeOnDelete();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->string('milestone')->nullable();
                $table->string('amount')->nullable();
                $table->decimal('percentage', 5, 2)->nullable();
                $table->string('due_upon')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('procurement_request_retentions')) {
            Schema::create('procurement_request_retentions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('procurement_request_id');
                $table->foreign('procurement_request_id', 'pr_retentions_request_fk')
                    ->references('id')->on('procurement_requests')->cascadeOnDelete();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->decimal('retention_percent', 5, 2)->nullable();
                $table->string('release_period')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('procurement_request_timeline_entries')) {
            Schema::create('procurement_request_timeline_entries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('procurement_request_id');
                $table->foreign('procurement_request_id', 'pr_timeline_request_fk')
                    ->references('id')->on('procurement_requests')->cascadeOnDelete();
                $table->string('activity', 80);
                $table->unsignedSmallInteger('duration_days')->nullable();
                $table->timestamps();

                $table->unique(['procurement_request_id', 'activity'], 'pr_timeline_activity_unique');
            });
        }

        if (! Schema::hasTable('procurement_request_approvals')) {
            Schema::create('procurement_request_approvals', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('procurement_request_id');
                $table->foreign('procurement_request_id', 'pr_approvals_request_fk')
                    ->references('id')->on('procurement_requests')->cascadeOnDelete();
                $table->string('role', 50);
                $table->string('name')->nullable();
                $table->string('signature')->nullable();
                $table->date('signed_at')->nullable();
                $table->timestamps();

                $table->unique(['procurement_request_id', 'role'], 'pr_approval_role_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::table('procurement_request_documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('procurement_request_id');
            $table->dropColumn(['document_type', 'file_description']);
        });

        Schema::dropIfExists('procurement_request_approvals');
        Schema::dropIfExists('procurement_request_timeline_entries');
        Schema::dropIfExists('procurement_request_retentions');
        Schema::dropIfExists('procurement_request_payment_terms');

        Schema::table('procurement_request_items', function (Blueprint $table) {
            $table->dropColumn(['item_name', 'unit_price', 'total_price']);
        });

        Schema::table('procurement_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subcategory_id');
            $table->dropConstrainedForeignId('category_id');
            $table->dropConstrainedForeignId('zone_id');
            $table->dropConstrainedForeignId('project_id');
            $table->dropColumn([
                'procurement_types',
                'geographic_scopes',
                'vendor_types',
                'justification',
                'delivery_lead_time_days',
                'delivery_location',
                'flexible_delivery_date',
                'currency_code',
                'samples_required',
                'scope_of_work',
                'nda_required',
                'primary_insurance_applicable',
                'final_insurance_applicable',
                'warranty_years',
                'warranty_coverage',
            ]);
        });
    }

    private function migrateExistingProcurementRequests(): void
    {
        $timelineActivities = [
            'rfq_issuance',
            'quotation_submission',
            'technical_evaluation',
            'commercial_evaluation',
            'negotiation',
            'approval_process',
            'contract_award',
            'po_issuance',
        ];

        $approvalRoles = ['requester', 'procurement', 'general_manager', 'received_by'];

        $requests = DB::table('procurement_requests')->orderBy('id')->get(['id']);

        foreach ($requests as $request) {
            $firstItem = DB::table('procurement_request_items')
                ->where('procurement_request_id', $request->id)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->first();

            $header = DB::table('procurement_requests')->where('id', $request->id)->first(['project_id']);

            if ($firstItem !== null && ($header?->project_id === null)) {
                $categoryId = $this->resolveCategoryId($firstItem->category ?? null);
                $subcategoryId = $this->resolveSubcategoryId($categoryId, $firstItem->subcategory ?? null);
                $vendorTypes = $this->scopeTypeToVendorTypesJson($firstItem->scope_type ?? null);

                DB::table('procurement_requests')
                    ->where('id', $request->id)
                    ->update([
                        'project_id' => $firstItem->project_id,
                        'zone_id' => $firstItem->zone_id,
                        'category_id' => $categoryId,
                        'subcategory_id' => $subcategoryId,
                        'vendor_types' => $vendorTypes,
                        'justification' => $firstItem->justification,
                        'scope_of_work' => $firstItem->scope_of_work,
                        'delivery_location' => $firstItem->delivery_location,
                        'flexible_delivery_date' => $firstItem->flexible_delivery_date ?? true,
                        'updated_at' => now(),
                    ]);
            }

            $items = DB::table('procurement_request_items')
                ->where('procurement_request_id', $request->id)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(['id', 'line_number', 'description', 'quantity', 'unit_price', 'total_price']);

            foreach ($items as $item) {
                $itemName = trim((string) ($item->line_number ?? ''));
                if ($itemName === '') {
                    $itemName = null;
                }

                $quantity = (float) ($item->quantity ?? 0);
                $unitPrice = $item->unit_price !== null ? (float) $item->unit_price : 0.0;
                $totalPrice = $item->total_price;
                if ($totalPrice === null && $quantity > 0) {
                    $totalPrice = round($quantity * $unitPrice, 4);
                }

                DB::table('procurement_request_items')
                    ->where('id', $item->id)
                    ->update([
                        'item_name' => $itemName,
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice,
                        'updated_at' => now(),
                    ]);
            }

            foreach ($timelineActivities as $activity) {
                DB::table('procurement_request_timeline_entries')->insertOrIgnore([
                    'procurement_request_id' => $request->id,
                    'activity' => $activity,
                    'duration_days' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($approvalRoles as $role) {
                DB::table('procurement_request_approvals')->insertOrIgnore([
                    'procurement_request_id' => $request->id,
                    'role' => $role,
                    'name' => null,
                    'signature' => null,
                    'signed_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function resolveCategoryId(?string $name): ?int
    {
        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }

        $id = DB::table('categories')
            ->whereNull('deleted_at')
            ->where(function ($query) use ($name) {
                $query->where('name_en', $name)->orWhere('name_ar', $name);
            })
            ->value('id');

        return $id !== null ? (int) $id : null;
    }

    private function resolveSubcategoryId(?int $categoryId, ?string $name): ?int
    {
        $name = trim((string) $name);
        if ($name === '' || $categoryId === null) {
            return null;
        }

        $id = DB::table('subcategories')
            ->whereNull('deleted_at')
            ->where('category_id', $categoryId)
            ->where(function ($query) use ($name) {
                $query->where('name_en', $name)->orWhere('name_ar', $name);
            })
            ->value('id');

        return $id !== null ? (int) $id : null;
    }

    private function scopeTypeToVendorTypesJson(?string $scopeType): ?string
    {
        $scopeType = trim((string) $scopeType);
        if ($scopeType === '') {
            return null;
        }

        $map = [
            'Contractor' => 'contractor',
            'Supplier' => 'supplier',
            'Studies' => 'studies',
        ];

        $values = [];
        foreach (preg_split('/\s*,\s*/', $scopeType) ?: [] as $part) {
            $part = trim($part);
            if ($part !== '' && isset($map[$part])) {
                $values[$map[$part]] = true;
            }
        }

        if ($values === []) {
            return null;
        }

        return json_encode(array_keys($values), JSON_THROW_ON_ERROR);
    }
};
