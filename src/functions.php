<?php

declare(strict_types=1);

namespace S2J\InquiryDestinationService;

use S2J\InquiryDestinationService\Core\Dispatch;
use S2J\InquiryDestinationService\Core\Payload;
use S2J\InquiryDestinationService\Core\Result;
use S2J\InquiryDestinationService\Core\Validate;

/**
 * @param array<string, mixed> $payload
 *
 * @return array<string, mixed>
 */
function normalize_inquiry_payload(array $payload): array
{
    return Payload::normalize($payload);
}

/**
 * @param array<string, mixed> $payload
 * @param array<string, mixed> $destination
 *
 * @return array{payload: array<string, mixed>, destination: array<string, mixed>, deficiencies: list<string>}
 */
function validate_inquiry_dispatch(array $payload, array $destination): array
{
    return Validate::inquiryDispatch($payload, $destination);
}

/**
 * @param array<string, mixed> $payload
 * @param array<string, mixed> $destination
 *
 * @return array{
 *     material: array{to: list<string>, subject: string, body: string, content_type: string},
 *     deficiencies: list<string>
 * }
 */
function build_dispatch_material(array $payload, array $destination): array
{
    $built = Dispatch::buildMaterial($payload, $destination);

    return [
        'material' => $built['material'],
        'deficiencies' => $built['deficiencies'],
    ];
}

/**
 * @param array<string, mixed> $context
 *
 * @return array<string, mixed>
 */
function map_dispatch_result(string $kind, mixed $execution_outcome, array $context = []): array
{
    return Result::map($kind, $execution_outcome, $context);
}
