<?php

declare(strict_types=1);

namespace S2J\InquiryDestinationService\Tests\Unit;

use PHPUnit\Framework\TestCase;

use function S2J\InquiryDestinationService\build_dispatch_material;
use function S2J\InquiryDestinationService\validate_inquiry_dispatch;

final class ContractSnapshotTest extends TestCase
{
    private const FIXTURES_DIR = __DIR__ . '/../Fixtures/contracts';

    public function testMailSalesBodyMatchesContractFixture(): void
    {
        $payload = [
            'inquiry_type' => 'sales',
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '+81-90-1234-5678',
            'message' => 'Hello',
            'subject_hint' => 'Product question',
            'locale' => 'ja',
            'meta' => [],
        ];
        $destination = [
            'kind' => 'mail',
            'inquiry_type' => 'sales',
            'mail_to' => ['team@example.com'],
            'saas_endpoint' => '',
            'saas_project_id' => '',
            'credentials' => [],
        ];

        $validated = validate_inquiry_dispatch($payload, $destination);
        $built = build_dispatch_material($validated['payload'], $validated['destination']);

        $fixturePath = self::FIXTURES_DIR . '/mail_sales_body.txt';
        $expected = file_get_contents($fixturePath);
        self::assertIsString($expected);

        self::assertSame($expected, $built['material']['body']);
    }
}
