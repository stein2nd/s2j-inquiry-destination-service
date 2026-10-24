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

        if ($kind === DestinationKinds::SAAS_BACKLOG || $kind === DestinationKinds::SAAS_ASANA) {
            return self::mapSaas($kind, $executionOutcome, $inquiryType, $correlationId);
        }

        throw new \InvalidArgumentException('Unsupported result kind.');
    }

    /**
     * @return array<string, mixed>
     */
    private static function mapMail(mixed $executionOutcome, string $inquiryType, string $correlationId): array
    {
        $success = $executionOutcome === true;

        return [
            'success' => $success,
            'inquiry_type' => $inquiryType,
            'kind' => DestinationKinds::MAIL,
            'external_ticket_id' => '',
            'failure_code' => $success ? '' : 'mail_rejected',
            'http_status' => null,
            'correlation_id' => $correlationId,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function mapSaas(
        string $kind,
        mixed $executionOutcome,
        string $inquiryType,
        string $correlationId
    ): array {
        if (! is_array($executionOutcome)) {
            throw new \InvalidArgumentException('SaaS execution_outcome must be an array.');
        }

        if (
            ! array_key_exists('http_status', $executionOutcome)
            || ! array_key_exists('parsed_body', $executionOutcome)
        ) {
            throw new \InvalidArgumentException('SaaS execution_outcome missing required keys.');
        }

        $httpStatus = (int) $executionOutcome['http_status'];
        $parsedBody = $executionOutcome['parsed_body'];
        if (! is_array($parsedBody)) {
            $parsedBody = [];
        }

        $ticketId = self::extractTicketId($kind, $parsedBody);
        $success = $httpStatus === 201 && $ticketId !== '';

        if ($success) {
            $failureCode = '';
        } elseif ($httpStatus < 200 || $httpStatus > 299) {
            $failureCode = 'saas_http_error';
        } else {
            $failureCode = 'saas_body_invalid';
        }

        return [
            'success' => $success,
            'inquiry_type' => $inquiryType,
            'kind' => $kind,
            'external_ticket_id' => $success ? $ticketId : '',
            'failure_code' => $failureCode,
            'http_status' => $httpStatus,
            'correlation_id' => $correlationId,
        ];
    }

    /**
     * @param array<string, mixed> $parsedBody
     */
    private static function extractTicketId(string $kind, array $parsedBody): string
    {
        if ($kind === DestinationKinds::SAAS_BACKLOG) {
            if (! array_key_exists('id', $parsedBody)) {
                return '';
            }

            $id = $parsedBody['id'];
            if (is_int($id) && $id > 0) {
                return (string) $id;
            }

            if (is_string($id) && trim($id) !== '') {
                return trim($id);
            }

            return '';
        }

        if ($kind === DestinationKinds::SAAS_ASANA) {
            $data = $parsedBody['data'] ?? null;
            if (! is_array($data)) {
                return '';
            }

            $gid = $data['gid'] ?? '';
            if (is_string($gid) && trim($gid) !== '') {
                return trim($gid);
            }
        }

        return '';
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
