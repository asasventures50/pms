@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500']) }}>
