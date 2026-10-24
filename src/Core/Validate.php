<?php

declare(strict_types=1);

namespace S2J\InquiryDestinationService\Core;

final class Validate
{
    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $destination
     *
     * @return array{payload: array<string, mixed>, destination: array<string, mixed>, deficiencies: list<string>}
     */
    public static function inquiryDispatch(array $payload, array $destination): array
    {
        $normalizedPayload = Payload::normalize($payload);
        $deficiencies = self::deficiencies($normalizedPayload, $destination);

        return [
            'payload' => $normalizedPayload,
            'destination' => $destination,
            'deficiencies' => $deficiencies,
        ];
    }

    /**
     * @param array<string, mixed> $payload normalized
     * @param array<string, mixed> $destination
     *
     * @return list<string>
     */
    public static function deficiencies(array $payload, array $destination): array
    {
        $codes = [];

        $inquiryType = $payload['inquiry_type'] ?? '';
        if (! is_string($inquiryType) || ! InquiryTypes::isValidSlug($inquiryType)) {
            $codes[] = 'inquiry_type_invalid';
        }

        if (($payload['name'] ?? '') === '') {
            $codes[] = 'name_empty';
        }

        $email = $payload['email'] ?? '';
        if ($email === '') {
            $codes[] = 'email_empty';
        } elseif (! self::isValidEmail($email)) {
            $codes[] = 'email_invalid';
        }

        if (($payload['message'] ?? '') === '') {
            $codes[] = 'message_empty';
        }

        $destinationInquiryType = self::destinationString($destination, 'inquiry_type');
        if ($destinationInquiryType !== $inquiryType) {
            $codes[] = 'destination_inquiry_type_mismatch';
        }

        $kind = self::destinationString($destination, 'kind');
        if (! DestinationKinds::isSupported($kind)) {
            $codes[] = 'destination_kind_unsupported';
        } else {
            self::appendKindSpecificDeficiencies($destination, $kind, $codes);
        }

        return $codes;
    }

    /**
     * @param array<string, mixed> $destination
     * @param list<string>         $codes
     */
    private static function appendKindSpecificDeficiencies(
        array $destination,
        string $kind,
        array &$codes
    ): void {
        if ($kind === DestinationKinds::MAIL) {
            if (self::validMailToCount($destination) === 0) {
                $codes[] = 'destination_empty';
            }

            return;
        }

        if ($kind === DestinationKinds::SAAS_BACKLOG) {
            if (self::destinationString($destination, 'saas_endpoint') === '') {
                $codes[] = 'destination_saas_endpoint_empty';
            }
            if (self::destinationString($destination, 'saas_project_id') === '') {
                $codes[] = 'destination_saas_project_id_empty';
            }
            if (! self::credentialNonEmpty($destination, 'api_key')) {
                $codes[] = 'destination_credentials_api_key_empty';
            }
            if (! self::credentialNonEmpty($destination, 'issue_type_id')) {
                $codes[] = 'destination_credentials_issue_type_id_empty';
            }
            if (! self::credentialNonEmpty($destination, 'priority_id')) {
                $codes[] = 'destination_credentials_priority_id_empty';
            }

            return;
        }

        if ($kind === DestinationKinds::SAAS_ASANA) {
            if (self::destinationString($destination, 'saas_project_id') === '') {
                $codes[] = 'destination_saas_project_id_empty';
            }
            if (! self::credentialNonEmpty($destination, 'access_token')) {
                $codes[] = 'destination_credentials_access_token_empty';
            }
        }
    }

    /**
     * @param array<string, mixed> $destination
     */
    private static function credentialNonEmpty(array $destination, string $key): bool
    {
        $credentials = $destination['credentials'] ?? null;
        if (! is_array($credentials)) {
            return false;
        }

        $value = $credentials[$key] ?? null;
        if (is_int($value)) {
            return $value > 0;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        return false;
    }

    /**
     * @param array<string, mixed> $destination
     */
    public static function validMailToCount(array $destination): int
    {
        $mailTo = $destination['mail_to'] ?? [];
        if (! is_array($mailTo)) {
            return 0;
        }

        $count = 0;
        foreach ($mailTo as $address) {
            if (! is_string($address)) {
                continue;
            }

            $trimmed = trim($address);
            if ($trimmed !== '' && self::isValidEmail($trimmed)) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * @param array<string, mixed> $destination
     *
     * @return list<string>
     */
    public static function normalizedMailTo(array $destination): array
    {
        $mailTo = $destination['mail_to'] ?? [];
        if (! is_array($mailTo)) {
            return [];
        }

        $valid = [];
        foreach ($mailTo as $address) {
            if (! is_string($address)) {
                continue;
            }

            $trimmed = trim($address);
            if ($trimmed !== '' && self::isValidEmail($trimmed)) {
                $valid[] = $trimmed;
            }
        }

        return $valid;
    }

    private static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * @param array<string, mixed> $destination
     */
    private static function destinationString(array $destination, string $key): string
    {
        $value = $destination[$key] ?? '';
        if (! is_string($value) && ! is_numeric($value)) {
            return '';
        }

        return trim((string) $value);
    }
}
