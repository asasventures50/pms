@php
    $label = $label ?? '';
    $required = $required ?? false;
    $hint = $hint ?? null;
    $options = $options ?? [];
    $valueParts = [];

    foreach ($options as $option) {
        $valueParts[] = ($option['checked'] ?? false ? '✓ ' : '').($option['label'] ?? '');
    }

    $valueLine = implode('  ', array_filter($valueParts, fn (string $part) => $part !== ''));
@endphp

<div class="po-form-group pr-form-option-group">
    <span class="po-form-label">
        {{ $label }}@if ($required)<span class="pr-form-option-required">*</span>@endif
    </span>
    <span class="po-form-line pr-form-option-line">{{ $valueLine }}</span>
</div>
@if ($hint)
    <p class="pr-form-option-hint">{{ $hint }}</p>
@endif
