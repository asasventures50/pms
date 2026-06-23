@php
    $bank = $buyer['bank'] ?? ['title' => 'معلومات البنك', 'lines' => []];
    $bankLines = array_values(array_filter($bank['lines'] ?? [], static fn ($line) => filled($line)));
@endphp

@if (count($bankLines) > 0)
    <div class="inv-bank-block">
        <div class="inv-bank-title">{{ $bank['title'] ?? 'معلومات البنك' }}</div>
        @foreach ($bankLines as $line)
            @php
                $colonPos = mb_strpos($line, ':');
                $label = $colonPos !== false ? trim(mb_substr($line, 0, $colonPos)) : '';
                $value = $colonPos !== false ? trim(mb_substr($line, $colonPos + 1)) : $line;
                $labelIsLatin = $label !== '' && preg_match('/\A[\x00-\x7F]+\z/u', $label) === 1;
            @endphp
            <div class="inv-bank-line">
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
    </div>
@endif
