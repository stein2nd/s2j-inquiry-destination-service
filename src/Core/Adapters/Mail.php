<?php

declare(strict_types=1);

namespace S2J\InquiryDestinationService\Core\Adapters;

use S2J\InquiryDestinationService\Core\InquiryTypes;
use S2J\InquiryDestinationService\Core\Validate;

final class Mail
{
    /**
     * @param array<string, mixed> $payload normalized
     * @param array<string, mixed> $destination
     *
     * @return array{to: list<string>, subject: string, body: string, content_type: string}
     */
    public static function buildMaterial(array $payload, array $destination): array
    {
        $inquiryType = (string) ($payload['inquiry_type'] ?? '');
        $subjectHint = (string) ($payload['subject_hint'] ?? '');
        $label = $subjectHint !== ''
            ? $subjectHint
            : InquiryTypes::subjectFallbackLabel($inquiryType);

        return [
            'to' => Validate::normalizedMailTo($destination),
            'subject' => sprintf('[%s] %s', $inquiryType, $label),
            'body' => self::buildBody($payload),
            'content_type' => 'text/plain',
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private static function buildBody(array $payload): string
    {
        $sections = [
            (string) ($payload['inquiry_type'] ?? ''),
            (string) ($payload['name'] ?? ''),
            (string) ($payload['email'] ?? ''),
        ];

        $phone = (string) ($payload['phone'] ?? '');
        if ($phone !== '') {
            $sections[] = $phone;
        }

        $sections[] = (string) ($payload['message'] ?? '');

        return implode("\n\n", $sections);
    }
}
