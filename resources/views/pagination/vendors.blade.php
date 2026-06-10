@if ($paginator->total() > 0)
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}">

        @if ($paginator->hasPages())
            <div class="mb-3 flex items-center justify-between gap-2 sm:hidden">
                @if ($paginator->onFirstPage())
                    <span class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium leading-5 text-gray-600 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        {!! __('pagination.previous') !!}
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium leading-5 text-gray-800 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-700 focus:border-blue-300 focus:outline-none focus:ring active:bg-gray-100 active:text-gray-800 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-900 dark:hover:text-gray-200">
                        {!! __('pagination.previous') !!}
                    </a>
                @endif

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium leading-5 text-gray-800 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-700 focus:border-blue-300 focus:outline-none focus:ring active:bg-gray-100 active:text-gray-800 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-900 dark:hover:text-gray-200">
                        {!! __('pagination.next') !!}
                    </a>
                @else
                    <span class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium leading-5 text-gray-600 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                        {!! __('pagination.next') !!}
                    </span>
                @endif
            </div>
        @endif

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                <p class="text-sm leading-5 text-gray-700 dark:text-gray-600">
                    {!! __('Showing') !!}
                    @if ($paginator->firstItem())
                        <span class="font-medium">{{ $paginator->firstItem() }}</span>
                        {!! __('to') !!}
                        <span class="font-medium">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    {!! __('of') !!}
                    <span class="font-medium">{{ $paginator->total() }}</span>
                    {!! __('results') !!}
                </p>

                <x-per-page-select id="vendor_per_page_select" />
            </div>

            @if ($paginator->hasPages())
                <div>
                    <span class="inline-flex rounded-md shadow-sm rtl:flex-row-reverse">
                        @if ($paginator->onFirstPage())
                            <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                                <span class="inline-flex cursor-not-allowed items-center rounded-l-md border border-gray-300 bg-white px-2 py-2 text-sm font-medium leading-5 text-gray-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400" aria-hidden="true">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </span>
                        @else
                            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center rounded-l-md border border-gray-300 bg-white px-2 py-2 text-sm font-medium leading-5 text-gray-500 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-400 focus:border-blue-300 focus:outline-none focus:ring active:bg-gray-100 active:text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-900 dark:hover:text-gray-300" aria-label="{{ __('pagination.previous') }}">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        @endif

                        @foreach ($elements as $element)
                            @if (is_string($element))
                                <span aria-disabled="true">
                                    <span class="-ml-px inline-flex cursor-default items-center border border-gray-300 bg-white px-4 py-2 text-sm font-medium leading-5 text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ $element }}</span>
                                </span>
                            @endif

                            @if (is_array($element))
                                @foreach ($element as $page => $url)
                                    @if ($page == $paginator->currentPage())
                                        <span aria-current="page">
                                            <span class="-ml-px inline-flex cursor-default items-center border border-gray-300 bg-gray-200 px-4 py-2 text-sm font-medium leading-5 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ $page }}</span>
                                        </span>
                                    @else
                                        <a href="{{ $url }}" class="-ml-px inline-flex items-center border border-gray-300 bg-white px-4 py-2 text-sm font-medium leading-5 text-gray-700 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-700 focus:border-blue-300 focus:outline-none focus:ring active:bg-gray-100 active:text-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-900 dark:hover:text-gray-300 dark:active:bg-gray-700 dark:focus:border-blue-800" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                            {{ $page }}
                                        </a>
                                    @endif
                                @endforeach
                            @endif
                        @endforeach

                        @if ($paginator->hasMorePages())
                            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="-ml-px inline-flex items-center rounded-r-md border border-gray-300 bg-white px-2 py-2 text-sm font-medium leading-5 text-gray-500 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-400 focus:border-blue-300 focus:outline-none focus:ring active:bg-gray-100 active:text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-900 dark:hover:text-gray-300" aria-label="{{ __('pagination.next') }}">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        @else
                            <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                                <span class="-ml-px inline-flex cursor-not-allowed items-center rounded-r-md border border-gray-300 bg-white px-2 py-2 text-sm font-medium leading-5 text-gray-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400" aria-hidden="true">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </span>
                        @endif
                    </span>
                </div>
            @endif
        </div>
    </nav>
@endif
