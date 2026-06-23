@php
    $notes = $invoice->displayNotes();
@endphp

@if (count($notes) > 0)
    <div class="inv-notes-block">
        <div class="inv-notes-title">ملاحظات:</div>
        <ul class="inv-notes-list">
            @foreach ($notes as $note)
                <li>{!! nl2br(e($note)) !!}</li>
            @endforeach
        </ul>
    </div>
@endif
