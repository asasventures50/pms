@php
    $buyer = $buyer ?? $company->details();
@endphp

<div class="inv-print-document">
    <table class="inv-print-table">
        <tbody>
            <tr>
                <td class="inv-print-cell">
                    <div class="inv-print-main">
                        <div class="inv-header">
                            <div class="inv-header-logo">
                                <img src="{{ $logoUrl }}" alt="{{ $company->label() }}" class="inv-logo-img"
                                     @unless ($logoExists)
                                         onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='block';"
                                     @endunless>
                                <div class="inv-logo-fallback" @if ($logoExists) style="display:none;" @endif>
                                    {!! $company->logoFallbackHtml() !!}
                                </div>
                            </div>
                            <div class="inv-header-title">إيصال دفع</div>
                        </div>

                        <div class="inv-meta-simple">
                            <div class="inv-meta-row">
                                <span class="inv-meta-label">رقم الإيصال</span>
                                <span class="inv-meta-value inv-ltr">{{ $receipt->code }}</span>
                            </div>
                            <div class="inv-meta-row">
                                <span class="inv-meta-label">التاريخ</span>
                                <span class="inv-meta-value">{{ $receipt->expense_date?->format('d-m-Y') }}</span>
                            </div>
                        </div>

                        <div class="inv-tables-block">
                            <div class="inv-table-frame">
                                <table class="inv-items-table">
                                    <thead>
                                    <tr>
                                        <th class="col-desc">البيان</th>
                                        <th style="width:18%;">التصنيف</th>
                                        <th style="width:18%;">المزود</th>
                                        <th class="col-total">المبلغ</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>
                                            <div style="font-weight:600;">{{ $receipt->title }}</div>
                                            @if ($receipt->description)
                                                <div style="margin-top:4px;color:#4b5563;white-space:pre-wrap;">{{ $receipt->description }}</div>
                                            @endif
                                        </td>
                                        <td style="text-align:center;">{{ $receipt->categoryLabel() }}</td>
                                        <td style="text-align:center;">{{ $receipt->provider_name ?: '—' }}</td>
                                        <td class="inv-ltr" style="text-align:center;font-weight:700;">{{ $receipt->formatAmount() }}</td>
                                    </tr>
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td colspan="3" style="text-align:left;font-weight:700;padding:10px;">الإجمالي</td>
                                        <td class="inv-ltr" style="text-align:center;font-weight:700;">{{ $receipt->formatAmount() }}</td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        @if ($receipt->approved_at)
                            <div class="inv-project-block" style="margin-top:14px;">
                                <span class="inv-project-label">تاريخ الموافقة:</span>
                                <span class="inv-project-name">{{ $receipt->approved_at->format('d-m-Y H:i') }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="inv-print-bottom">
                        @include('procurement.quick-receipts.print._pre_footer')
                    </div>
                </td>
            </tr>
        </tbody>
        <tfoot class="inv-print-tfoot">
            <tr>
                <td class="inv-print-cell">
                    <div class="inv-footer-space" aria-hidden="true"></div>
                </td>
            </tr>
        </tfoot>
    </table>

    @include('procurement.invoices.print._footer')
</div>
