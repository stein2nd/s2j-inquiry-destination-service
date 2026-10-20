<?php

declare(strict_types=1);

namespace S2J\InquiryDestinationService\Core;

final class InquiryTypes
{
    public const SALES = 'sales';

    public const RECRUIT = 'recruit';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [self::SALES, self::RECRUIT];
    }

    public static function isValid(string $inquiryType): bool
    {
        return in_array($inquiryType, self::all(), true);
    }

    public static function subjectFallbackLabel(string $inquiryType): string
    {
        return match ($inquiryType) {
            self::SALES => 'Sales inquiry',
            self::RECRUIT => 'Recruit inquiry',
            default => $inquiryType,
        };
    }
}
