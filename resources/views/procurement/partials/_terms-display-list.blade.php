@php
    $terms = $terms ?? [];
    $termsLocale = $termsLocale ?? 'en';
    $termsRtl = $termsLocale === 'ar';
    $listClass = $listClass ?? 'mt-3 list-none space-y-1.5';
    $compact = $compact ?? false;
@endphp

<ul @class([$listClass, 'proc-terms-display-list', 'proc-terms-display-list--compact' => $compact, 'proc-terms-display-list--rtl' => $termsRtl])
    @if ($termsRtl) dir="rtl" lang="ar" @endif>
    @forelse ($terms as $term)
        @php
            $termText = trim((string) $term);
            $parts = explode(':', $termText, 2);
            $hasKeyValue = count($parts) === 2 && trim($parts[0]) !== '' && trim($parts[1]) !== '';
            $termKey = $hasKeyValue ? trim($parts[0]) : '';
            $termValue = $hasKeyValue ? trim($parts[1]) : $termText;
        @endphp
        <li class="flex gap-2" @if ($termsRtl) lang="ar" @endif>
            <span class="shrink-0">-</span>
            <span class="min-w-0 flex-1">
                @if ($hasKeyValue)
                    <strong class="proc-term-key">{{ $termKey }}:</strong> {{ $termValue }}
                @else
                    {{ $termText }}
                @endif
            </span>
        </li>
    @empty
        <li class="text-slate-500">No terms specified.</li>
    @endforelse
</ul>

@once
    <style>
        .proc-terms-display-list--compact {
            font-size: 9px;
            line-height: 1.35;
        }

        .proc-terms-display-list--compact .proc-term-key {
            font-weight: bold;
        }
    </style>
@endonce
