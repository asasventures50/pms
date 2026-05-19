@php
    $terms = $terms ?? \App\Support\Procurement\RfqTerms::defaults();
@endphp

<section class="mt-8">
    <h3 class="text-sm font-bold uppercase tracking-wide text-slate-900">Terms &amp; conditions</h3>
    <ul class="mt-3 list-none space-y-1.5 text-sm text-slate-800">
        @foreach ($terms as $term)
            <li class="flex gap-2">
                <span class="shrink-0">-</span>
                <span>{{ $term }}</span>
            </li>
        @endforeach
    </ul>
</section>
