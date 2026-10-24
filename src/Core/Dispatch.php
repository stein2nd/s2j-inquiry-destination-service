<?php

declare(strict_types=1);

namespace S2J\InquiryDestinationService\Core;

use S2J\InquiryDestinationService\Core\Adapters\Mail;
use S2J\InquiryDestinationService\Core\Adapters\SaasAsana;
use S2J\InquiryDestinationService\Core\Adapters\SaasBacklog;

final class Dispatch
{
    /**
     * @param array<string, mixed> $payload normalized
     * @param array<string, mixed> $destination
     *
     * @return array{material: array<string, mixed>, deficiencies: list<string>}
     */
    public static function buildMaterial(array $payload, array $destination): array
    {
        $deficiencies = Validate::deficiencies($payload, $destination);
        if ($deficiencies !== []) {
            throw new \InvalidArgumentException(
                'build_dispatch_material requires validated payload and destination.'
            );
        }

        $kind = (string) ($destination['kind'] ?? '');

        $material = match ($kind) {
            DestinationKinds::MAIL => Mail::buildMaterial($payload, $destination),
            DestinationKinds::SAAS_BACKLOG => SaasBacklog::buildMaterial($payload, $destination),
            DestinationKinds::SAAS_ASANA => SaasAsana::buildMaterial($payload, $destination),
            default => throw new \InvalidArgumentException('Unsupported destination kind.'),
        };

        return [
            'material' => $material,
            'deficiencies' => [],
        ];
    }
}
