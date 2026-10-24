<?php

declare(strict_types=1);

namespace S2J\InquiryDestinationService\Core\Adapters;

final class SaasBacklog
{
    /**
     * @param array<string, mixed> $payload normalized
     * @param array<string, mixed> $destination
     *
     * @return array{method: string, url: string, headers: array<string, string>, body: string}
     */
    public static function buildMaterial(array $payload, array $destination): array
    {
        $endpoint = rtrim(self::destinationString($destination, 'saas_endpoint'), '/');
        $credentials = self::credentialsArray($destination);

        $form = [
            'projectId' => (string) ($destination['saas_project_id'] ?? ''),
            'summary' => InquiryContent::subjectLine($payload),
            'description' => InquiryContent::plainBody($payload),
            'issueTypeId' => self::credentialString($credentials, 'issue_type_id'),
            'priorityId' => self::credentialString($credentials, 'priority_id'),
        ];

        return [
            'method' => 'POST',
            'url' => $endpoint . '/api/v2/issues',
            'headers' => [
                'Backlog-API-Key' => self::credentialString($credentials, 'api_key'),
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => http_build_query($form, '', '&', PHP_QUERY_RFC3986),
        ];
    }

    /**
     * @param array<string, mixed> $destination
     *
     * @return array<string, mixed>
     */
    private static function credentialsArray(array $destination): array
    {
        $credentials = $destination['credentials'] ?? [];
        if (! is_array($credentials)) {
            return [];
        }

        return $credentials;
    }

    /**
     * @param array<string, mixed> $credentials
     */
    private static function credentialString(array $credentials, string $key): string
    {
        $value = $credentials[$key] ?? '';
        if (is_int($value) && $value > 0) {
            return (string) $value;
        }

        if (is_string($value)) {
            return trim($value);
        }

        return '';
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
