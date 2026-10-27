<?php

declare(strict_types=1);

namespace S2J\InquiryDestinationService\Core\Adapters;

use S2J\InquiryDestinationService\Core\Validate;

/** Mail `kind` 材料 — 4キーのみ (Transport / From 等は含めない。docs/core/transport_boundary_spec.md)。 */
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
        return [
            'to' => Validate::normalizedMailTo($destination),
            'subject' => InquiryContent::subjectLine($payload),
            'body' => InquiryContent::plainBody($payload),
            'content_type' => 'text/plain',
        ];
    }
}
