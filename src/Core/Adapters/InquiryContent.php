<?php

declare(strict_types=1);

namespace S2J\InquiryDestinationService\Core\Adapters;

/**
 * Mail / SaaS 共通の件名・プレーンテキスト本文 (mail_adapter_spec / saas_* adapter spec)。
 */
final class InquiryContent
{
    /**
     * @param array<string, mixed> $payload normalized
     */
    public static function subjectLine(array $payload): string
    {
        $inquiryType = (string) ($payload['inquiry_type'] ?? '');
        $subjectHint = (string) ($payload['subject_hint'] ?? '');
        $label = $subjectHint !== '' ? $subjectHint : $inquiryType;

        return sprintf('[%s] %s', $inquiryType, $label);
    }

    /**
     * @param array<string, mixed> $payload normalized
     */
    public static function plainBody(array $payload): string
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
