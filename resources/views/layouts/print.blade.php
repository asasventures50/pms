<!DOCTYPE html>
<html lang="@yield('html_lang', 'en')">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Print') — PMS</title>
    @include('partials.favicon')
    @stack('styles')
</head>
<body class="po-print-body">
    @yield('content')
    @stack('scripts')
</body>
</html>
