<?php

declare(strict_types=1);

namespace S2J\InquiryDestinationService\Core;

final class Result
{
    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public static function map(string $kind, mixed $executionOutcome, array $context = []): array
    {
        $inquiryType = self::contextString($context, 'inquiry_type');
        $correlationId = self::contextString($context, 'correlation_id');

        if ($kind === DestinationKinds::MAIL) {
            return self::mapMail($executionOutcome, $inquiryType, $correlationId);
        }

        throw new \InvalidArgumentException('Unsupported result kind.');
    }

    /**
     * @return array<string, mixed>
     */
    private static function mapMail(mixed $executionOutcome, string $inquiryType, string $correlationId): array
    {
        $success = $executionOutcome === true;

        $record = [
            'success' => $success,
            'inquiry_type' => $inquiryType,
            'kind' => DestinationKinds::MAIL,
            'external_ticket_id' => '',
            'failure_code' => $success ? '' : 'mail_rejected',
            'http_status' => null,
            'correlation_id' => $correlationId,
        ];

        return $record;
    }

    /**
     * @param array<string, mixed> $context
     */
    private static function contextString(array $context, string $key): string
    {
        $value = $context[$key] ?? '';
        if (! is_string($value) && ! is_numeric($value)) {
            return '';
        }

        return trim((string) $value);
    }
}
