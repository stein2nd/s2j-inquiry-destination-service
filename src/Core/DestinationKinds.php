<?php

declare(strict_types=1);

namespace S2J\InquiryDestinationService\Core;

final class DestinationKinds
{
    public const MAIL = 'mail';

    /**
     * @return list<string>
     */
    public static function supported(): array
    {
        return [self::MAIL];
    }

    public static function isSupported(string $kind): bool
    {
        return in_array($kind, self::supported(), true);
    }
}
