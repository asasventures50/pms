<?php

namespace App\Exports\Procurement;

use App\Models\Procurement\Vendors\Vendor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class VendorsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithTitle
{
    /**
     * @param  Builder<Vendor>  $query
     */
    public function __construct(
        private Builder $query
    ) {}

    public function collection(): Collection
    {
        $rows = collect();

        // reorder('id') is required: list query uses latest(), and chunkById +
        // created_at ordering skips most rows (e.g. ~400 of 893).
        $this->query->reorder('id')->chunkById(200, function ($vendors) use ($rows) {
            foreach ($vendors as $vendor) {
                $rows->push($this->mapVendor($vendor));
            }
        });

        return $rows;
    }

    /**
     * @return list<mixed>
     */
    private function mapVendor(Vendor $vendor): array
    {
        $primaryLoc = $vendor->locations->firstWhere('is_primary', true) ?? $vendor->locations->first();

        $primaryAssignments = $vendor->vendorCategories->where('is_primary', true);
        $primaryCategoriesAr = $primaryAssignments->map(function ($vc) {
            return collect([$vc->category?->name_ar, $vc->subcategory?->name_ar])->filter()->join(' / ');
        })->filter()->join('; ');

        $primaryCategoriesEn = $primaryAssignments->map(function ($vc) {
            return collect([$vc->category?->name_en, $vc->subcategory?->name_en])->filter()->join(' / ');
        })->filter()->join('; ');

        $businessTypes = $vendor->businessTypes->map(function ($row) {
            $value = $row->business_type;
            $raw = $value instanceof \BackedEnum ? $value->value : (string) $value;

            return Str::headline(str_replace('_', ' ', $raw));
        })->join(', ');

        $rfqMethods = is_array($vendor->rfq_method)
            ? collect($vendor->rfq_method)
                ->map(fn ($m) => Str::headline(str_replace('_', ' ', (string) $m)))
                ->join(', ')
            : '';

        $language = $vendor->language instanceof \BackedEnum
            ? strtoupper($vendor->language->value)
            : strtoupper((string) $vendor->language);

        $status = $vendor->status instanceof \BackedEnum
            ? $vendor->status->value
            : (string) $vendor->status;

        return [
            $vendor->vendor_code,
            $vendor->creator?->name,
            $vendor->name,
            $language,
            $primaryLoc?->country?->name,
            $primaryLoc?->city?->name,
            $vendor->phone,
            $vendor->email,
            $rfqMethods,
            Str::headline(str_replace('_', ' ', $status)),
            $primaryCategoriesAr,
            $primaryCategoriesEn,
            $businessTypes,
            ($vendor->brochures_count ?? 0) > 0 ? 'Yes' : 'No',
        ];
    }

    public function headings(): array
    {
        return [
            'Vendor Code',
            'Created By',
            'Vendor Name',
            'Language',
            'Country',
            'City',
            'Phone',
            'Email',
            'RFQ Methods',
            'Status',
            'Primary Categories (AR)',
            'Primary Categories (EN)',
            'Business Types',
            'Brochures',
        ];
    }

    public function title(): string
    {
        return 'vendors';
    }
}
