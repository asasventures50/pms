@php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
    $hideLocaleToggle = $hideLocaleToggle ?? false;
    $switchLocaleUrl = fn (string $lang) => request()->fullUrlWithQuery(['lang' => $lang]);
    $nextLocale = $locale === 'ar' ? 'en' : 'ar';
    $switchLabel = $nextLocale === 'ar'
        ? __('vendor_registration.switch_to_arabic')
        : __('vendor_registration.switch_to_english');
    $nextLocaleShort = $nextLocale === 'ar' ? 'ع' : 'EN';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $locale) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" @class(['public-form-rtl' => $isRtl])>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#E65100">

    <title>@yield('title', 'PMS') — ASAS Ventures</title>
    @include('partials.favicon')

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700{{ $isRtl ? '|noto-sans-arabic:400,500,600,700' : '' }}" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="public-form-page min-h-screen font-sans text-slate-900 antialiased">
    <header class="public-form-header border-b bg-white">
        <div class="relative mx-auto flex min-h-[5.5rem] max-w-5xl items-center justify-center px-14 py-5 sm:px-16 sm:py-5 lg:px-20">
            <img src="{{ asset('images/po/logo.png') }}" alt="ASAS Ventures" class="public-form-logo h-auto w-auto max-h-20">
            @unless ($hideLocaleToggle)
                <a href="{{ $switchLocaleUrl($nextLocale) }}"
                   class="public-form-lang-toggle absolute end-4 top-1/2 z-10 inline-flex h-9 -translate-y-1/2 items-center gap-2 rounded-full border border-[color-mix(in_oklab,#e65100_22%,#e2e8f0)] bg-white px-3 text-[#e65100] shadow-sm transition duration-200 hover:border-[color-mix(in_oklab,#e65100_40%,#e2e8f0)] hover:bg-[color-mix(in_oklab,#ff9800_10%,white)] hover:shadow-md sm:end-6 lg:end-8"
                   title="{{ $switchLabel }}"
                   aria-label="{{ $switchLabel }}">
                    <svg class="size-[18px] shrink-0" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.6 9h16.8M3.6 15h16.8" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.2 2.4 3.4 5.5 3.4 9s-1.2 6.6-3.4 9c-2.2-2.4-3.4-5.5-3.4-9s1.2-6.6 3.4-9Z" />
                    </svg>
                    <span class="h-4 w-px shrink-0 bg-[color-mix(in_oklab,#e65100_22%,#e2e8f0)]" aria-hidden="true"></span>
                    <span class="min-w-[1.25rem] text-center text-[11px] font-bold leading-none tracking-wide" dir="auto">{{ $nextLocaleShort }}</span>
                </a>
            @endunless
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
