<?php

declare(strict_types=1);

namespace S2J\InquiryDestinationService\Core;

final class Payload
{
    /**
     * @var list<string>
     */
    private const STRING_KEYS = [
        'inquiry_type',
        'name',
        'email',
        'phone',
        'message',
        'subject_hint',
        'locale',
    ];

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public static function normalize(array $payload): array
    {
        $normalized = [];

        foreach (self::STRING_KEYS as $key) {
            $normalized[$key] = self::normalizeString($payload[$key] ?? '');
        }

        $meta = $payload['meta'] ?? [];
        if (! is_array($meta)) {
            $meta = [];
        }

        $normalized['meta'] = $meta;

        return $normalized;
    }

    private static function normalizeString(mixed $value): string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return '';
        }

        return trim((string) $value);
    }
}
