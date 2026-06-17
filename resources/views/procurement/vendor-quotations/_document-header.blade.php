@php
    use App\Enums\Procurement\PrCompany;

    if (! isset($company) && isset($rfqContext['procurement_request'])) {
        $company = PrCompany::resolve($rfqContext['procurement_request']->company_key);
    }

    $company ??= PrCompany::AsasVentures;
@endphp

<table class="w-full border-2 border-slate-900 border-collapse text-slate-900">
    <tr>
        <td class="w-[22%] min-h-[5rem] border border-slate-900 p-2 align-middle text-center">
            @include('procurement._document-logo-cell', ['company' => $company])
        </td>
        <td class="border border-slate-900 p-4 text-center align-middle">
            <p class="text-xl font-bold tracking-tight sm:text-2xl">Vendor Quotation</p>
            <p class="mt-1 text-xs font-medium uppercase tracking-wide text-slate-600">Procurement Department</p>
        </td>
    </tr>
</table>
