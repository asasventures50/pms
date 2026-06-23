@php
    $projectManagerName = trim((string) ($invoice->project_manager_name ?? ''));
    $recipientName = trim((string) ($invoice->recipient_name ?? ''));
@endphp

<div class="inv-pre-footer">
    <div class="inv-pre-footer-col inv-pre-footer-signature inv-pre-footer-signature--manager">
        <span class="inv-signature-label">اسم مدير المشروع</span>
        @if ($projectManagerName !== '')
            <span class="inv-signature-name">{{ $projectManagerName }}</span>
        @endif
        <div class="inv-signature-line"></div>
        <span class="inv-signature-caption">التوقيع:</span>
        <div class="inv-signature-pad" aria-hidden="true"></div>
    </div>

    <div class="inv-pre-footer-col inv-pre-footer-signature inv-pre-footer-signature--recipient">
        <span class="inv-signature-label">اسم المستلم</span>
        @if ($recipientName !== '')
            <span class="inv-signature-name">{{ $recipientName }}</span>
        @endif
        <div class="inv-signature-line"></div>
        <span class="inv-signature-caption">التوقيع:</span>
        <div class="inv-signature-pad" aria-hidden="true"></div>
    </div>
</div>
