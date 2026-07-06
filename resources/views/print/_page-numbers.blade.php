@php
    use App\Support\PrintPageNumberCss;

    $pageSize = $pageSize ?? 'A4 portrait';
    $marginTop = $marginTop ?? '10mm';
    $marginRight = $marginRight ?? '10mm';
    $marginBottom = $marginBottom ?? '16mm';
    $marginLeft = $marginLeft ?? '10mm';
@endphp

@push('styles')
    {!! PrintPageNumberCss::styleTag($pageSize, $marginTop, $marginRight, $marginBottom, $marginLeft) !!}
@endpush
