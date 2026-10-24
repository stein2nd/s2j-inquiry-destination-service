<?php

declare(strict_types=1);

namespace S2J\InquiryDestinationService\Core\Adapters;

final class SaasAsana
{
    private const DEFAULT_BASE = 'https://app.asana.com';

    /**
     * @param array<string, mixed> $payload normalized
     * @param array<string, mixed> $destination
     *
     * @return array{method: string, url: string, headers: array<string, string>, body: string}
     */
    public static function buildMaterial(array $payload, array $destination): array
    {
        $base = rtrim(self::destinationString($destination, 'saas_endpoint'), '/');
        if ($base === '') {
            $base = self::DEFAULT_BASE;
        }

        $projectGid = self::destinationString($destination, 'saas_project_id');
        $credentials = self::credentialsArray($destination);

        $data = [
            'name' => InquiryContent::subjectLine($payload),
            'notes' => InquiryContent::plainBody($payload),
            'projects' => [$projectGid],
            'completed' => false,
        ];

        $encoded = json_encode(['data' => $data], JSON_THROW_ON_ERROR);

        return [
            'method' => 'POST',
            'url' => $base . '/api/1.0/tasks',
            'headers' => [
                'Authorization' => 'Bearer ' . self::credentialString($credentials, 'access_token'),
                'Content-Type' => 'application/json',
            ],
            'body' => $encoded,
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
