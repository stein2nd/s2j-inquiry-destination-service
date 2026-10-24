<?php

declare(strict_types=1);

namespace S2J\InquiryDestinationService\Core;

final class DestinationKinds
{
    public const MAIL = 'mail';

    public const SAAS_BACKLOG = 'saas_backlog';

    public const SAAS_ASANA = 'saas_asana';

    /**
     * @return list<string>
     */
    public static function supported(): array
    {
        return [
            self::MAIL,
            self::SAAS_BACKLOG,
            self::SAAS_ASANA,
        ];
    }

    public static function isSupported(string $kind): bool
    {
        return in_array($kind, self::supported(), true);
    }
}
