@php
    $termsLocale = $printLabels->locale();
    $termsRtl = $printLabels->isRtl();
    $terms = $terms ?? [];
@endphp

<div @class(['po-terms-block', 'po-terms-block--rtl' => $termsRtl, 'inv-sow-terms-block' => true]) @if ($termsRtl) dir="rtl" lang="ar" @endif>
    <div class="po-field-label">{{ $printLabels->t('terms_and_conditions') }}</div>
    @if (count($terms) > 0)
        <ul class="po-terms-list">
            @foreach ($terms as $term)
                @php
                    $termText = trim((string) $term);
                    $parts = explode(':', $termText, 2);
                    $hasKeyValue = count($parts) === 2 && trim($parts[0]) !== '' && trim($parts[1]) !== '';
                    $termKey = $hasKeyValue ? trim($parts[0]) : '';
                    $termValue = $hasKeyValue ? trim($parts[1]) : $termText;
                @endphp
                <li @if ($termsRtl) lang="ar" @endif>
                    @if ($hasKeyValue)
                        <strong class="po-term-key">{{ $termKey }}:</strong> {{ $termValue }}
                    @else
                        {{ $termText }}
                    @endif
                </li>
            @endforeach
        </ul>
    @else
        <div class="po-field-value po-field-value--empty"></div>
    @endif
</div>
