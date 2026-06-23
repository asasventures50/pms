@php
    $bank = $buyer['bank'] ?? ['title' => 'معلومات البنك', 'lines' => []];
    $bankLines = array_values(array_filter($bank['lines'] ?? [], static fn ($line) => filled($line)));
    $projectManagerName = trim((string) ($invoice->project_manager_name ?? ''));
    $recipientName = trim((string) ($invoice->recipient_name ?? ''));
@endphp

<div class="inv-pre-footer">
    <div class="inv-pre-footer-col inv-pre-footer-bank">
        @if (count($bankLines) > 0)
            <div class="inv-pre-footer-bank-title">{{ $bank['title'] ?? 'معلومات البنك' }}</div>
            @foreach ($bankLines as $line)
                @php
                    $colonPos = mb_strpos($line, ':');
                    $label = $colonPos !== false ? trim(mb_substr($line, 0, $colonPos)) : '';
                    $value = $colonPos !== false ? trim(mb_substr($line, $colonPos + 1)) : $line;
                    $labelIsLatin = $label !== '' && preg_match('/\A[\x00-\x7F]+\z/u', $label) === 1;
                @endphp
                <div class="inv-pre-footer-bank-line">
                    @if ($colonPos !== false)
                        @if ($labelIsLatin)
                            <span class="inv-ltr" dir="ltr"><strong>{{ $label }}:</strong> {{ $value }}</span>
                        @else
                            <strong>{{ $label }}:</strong>
                            @if (preg_match('/\A[\x00-\x7F]+\z/u', $value) === 1)
                                <bdi class="inv-ltr" dir="ltr">{{ $value }}</bdi>
                            @else
                                {{ $value }}
                            @endif
                        @endif
                    @else
                        {{ $line }}
                    @endif
                </div>
            @endforeach
        @endif
    </div>

    <div class="inv-pre-footer-col inv-pre-footer-signature">
        <span class="inv-signature-label">اسم مدير المشروع</span>
        @if ($projectManagerName !== '')
            <span class="inv-signature-name">{{ $projectManagerName }}</span>
        @endif
        <div class="inv-signature-line"></div>
        <span class="inv-signature-caption">التوقيع:</span>
        <div class="inv-signature-pad" aria-hidden="true"></div>
    </div>

    <div class="inv-pre-footer-col inv-pre-footer-signature">
        <span class="inv-signature-label">اسم المستلم</span>
        @if ($recipientName !== '')
            <span class="inv-signature-name">{{ $recipientName }}</span>
        @endif
        <div class="inv-signature-line"></div>
        <span class="inv-signature-caption">التوقيع:</span>
        <div class="inv-signature-pad" aria-hidden="true"></div>
    </div>
</div>
