<?php

namespace App\Support;

class TextDirection
{
    public static function isRtl(string $text): bool
    {
        return (bool) preg_match('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}]/u', $text);
    }
}
