<?php

declare(strict_types=1);

namespace S2J\InquiryDestinationService\Core;

final class InquiryTypes
{
    /**
     * 小文字英字始まり。英数字と `_` のみ。最大64文字 (正規表現上 1 + 0–63)。
     */
    public const SLUG_PATTERN = '/^[a-z][a-z0-9_]{0,63}$/';

    public static function isValidSlug(string $inquiryType): bool
    {
        if ($inquiryType === '') {
            return false;
        }

        return preg_match(self::SLUG_PATTERN, $inquiryType) === 1;
    }
}
