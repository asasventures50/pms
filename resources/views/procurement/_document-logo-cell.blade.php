@php
    use App\Enums\Procurement\PrCompany;

    $company = $company ?? PrCompany::AsasVentures;
@endphp

<div class="flex min-h-[4rem] items-center justify-center">
    <img src="{{ $company->logoUrl() }}" alt="{{ $company->label() }}"
         class="max-h-16 max-w-full object-contain"
         @unless ($company->logoExists())
             onerror="this.style.display='none';this.nextElementSibling.style.display='block';"
         @endunless>
    <div class="text-xs font-bold leading-tight text-slate-800"
         @if ($company->logoExists()) style="display:none;" @endif>
        {!! $company->logoFallbackHtml() !!}
    </div>
</div>
