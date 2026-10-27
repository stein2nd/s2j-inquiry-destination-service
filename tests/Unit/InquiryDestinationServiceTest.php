<?php

declare(strict_types=1);

namespace S2J\InquiryDestinationService\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function S2J\InquiryDestinationService\build_dispatch_material;
use function S2J\InquiryDestinationService\map_dispatch_result;
use function S2J\InquiryDestinationService\normalize_inquiry_payload;
use function S2J\InquiryDestinationService\validate_inquiry_dispatch;

final class InquiryDestinationServiceTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'inquiry_type' => 'sales',
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '',
            'message' => 'Hello',
            'subject_hint' => 'Product question',
            'locale' => 'ja',
            'meta' => [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validDestination(): array
    {
        return [
            'kind' => 'mail',
            'inquiry_type' => 'sales',
            'mail_to' => ['team@example.com'],
            'saas_endpoint' => '',
            'saas_project_id' => '',
            'credentials' => [],
        ];
    }

    public function testNormalizeTrimsStringsAndCoercesMeta(): void
    {
        $normalized = normalize_inquiry_payload([
            'inquiry_type' => ' sales ',
            'name' => ' Jane ',
            'email' => 'jane@example.com',
            'message' => 'Hi',
            'meta' => 'not-an-array',
            'unknown_key' => 'drop-me',
        ]);

        self::assertSame('sales', $normalized['inquiry_type']);
        self::assertSame('Jane', $normalized['name']);
        self::assertSame([], $normalized['meta']);
        self::assertArrayNotHasKey('unknown_key', $normalized);
    }

    public function testValidateReturnsStableDeficiencyOrder(): void
    {
        $result = validate_inquiry_dispatch(
            [
                'inquiry_type' => '',
                'name' => '',
                'email' => 'bad',
                'message' => '',
            ],
            [
                'kind' => 'slack',
                'inquiry_type' => 'recruit',
                'mail_to' => [],
            ]
        );

        self::assertSame(
            [
                'inquiry_type_invalid',
                'name_empty',
                'email_invalid',
                'message_empty',
                'destination_inquiry_type_mismatch',
                'destination_kind_unsupported',
            ],
            $result['deficiencies']
        );
    }

    public function testValidateReturnsDestinationUnchanged(): void
    {
        $destination = [
            'kind' => 'mail',
            'inquiry_type' => 'sales',
            'mail_to' => ['  team@example.com  '],
            'saas_endpoint' => '',
            'saas_project_id' => '',
            'credentials' => [],
            'plugin_only_key' => 'preserve',
        ];

        $result = validate_inquiry_dispatch($this->validPayload(), $destination);

        self::assertSame([], $result['deficiencies']);
        self::assertSame($destination, $result['destination']);
        self::assertSame('Jane Doe', $result['payload']['name']);
    }

    public function testRecruitHappyPathValidateAndBuild(): void
    {
        $payload = [
            'inquiry_type' => 'recruit',
            'name' => 'Alex',
            'email' => 'alex@example.com',
            'phone' => '',
            'message' => 'Application',
            'subject_hint' => '',
            'locale' => 'ja',
            'meta' => [],
        ];
        $destination = [
            'kind' => 'mail',
            'inquiry_type' => 'recruit',
            'mail_to' => ['hr@example.com'],
            'saas_endpoint' => '',
            'saas_project_id' => '',
            'credentials' => [],
        ];

        $validated = validate_inquiry_dispatch($payload, $destination);
        self::assertSame([], $validated['deficiencies']);

        $built = build_dispatch_material($validated['payload'], $validated['destination']);
        self::assertSame(['hr@example.com'], $built['material']['to']);
        self::assertSame('[recruit] recruit', $built['material']['subject']);
    }

    public function testBuildUsesSubjectFallbackWhenSubjectHintEmpty(): void
    {
        $payload = $this->validPayload();
        $payload['subject_hint'] = '';

        $validated = validate_inquiry_dispatch($payload, $this->validDestination());
        $built = build_dispatch_material($validated['payload'], $validated['destination']);

        self::assertSame('[sales] sales', $built['material']['subject']);
    }

    public function testCustomInquiryTypeSlugIsAccepted(): void
    {
        $payload = [
            'inquiry_type' => 'product_feedback',
            'name' => 'Sam',
            'email' => 'sam@example.com',
            'phone' => '',
            'message' => 'Feedback',
            'subject_hint' => 'Product feedback',
            'locale' => 'ja',
            'meta' => [],
        ];
        $destination = [
            'kind' => 'mail',
            'inquiry_type' => 'product_feedback',
            'mail_to' => ['feedback@example.com'],
            'saas_endpoint' => '',
            'saas_project_id' => '',
            'credentials' => [],
        ];

        $validated = validate_inquiry_dispatch($payload, $destination);
        self::assertSame([], $validated['deficiencies']);

        $built = build_dispatch_material($validated['payload'], $validated['destination']);
        self::assertSame('[product_feedback] Product feedback', $built['material']['subject']);
    }

    public function testDestinationEmptyWhenMailToHasOnlyInvalidAddresses(): void
    {
        $result = validate_inquiry_dispatch(
            $this->validPayload(),
            [
                'kind' => 'mail',
                'inquiry_type' => 'sales',
                'mail_to' => ['', 'not-an-email'],
            ]
        );

        self::assertContains('destination_empty', $result['deficiencies']);
    }

    public function testBuildDispatchMaterialProducesMailMaterial(): void
    {
        $validated = validate_inquiry_dispatch($this->validPayload(), $this->validDestination());
        $built = build_dispatch_material($validated['payload'], $validated['destination']);

        self::assertSame(['team@example.com'], $built['material']['to']);
        self::assertSame('[sales] Product question', $built['material']['subject']);
        self::assertSame(
            "sales\n\nJane Doe\n\njane@example.com\n\nHello",
            $built['material']['body']
        );
        self::assertSame('text/plain', $built['material']['content_type']);
        self::assertSame([], $built['deficiencies']);
        $materialKeys = array_keys($built['material']);
        sort($materialKeys);
        self::assertSame(['body', 'content_type', 'subject', 'to'], $materialKeys);
    }

    public function testBuildDispatchMaterialThrowsWhenNotValidated(): void
    {
        $this->expectException(InvalidArgumentException::class);

        build_dispatch_material(
            ['inquiry_type' => 'sales', 'name' => '', 'email' => '', 'message' => ''],
            $this->validDestination()
        );
    }

    public function testMapDispatchResultForMail(): void
    {
        $success = map_dispatch_result('mail', true, [
            'inquiry_type' => 'sales',
            'correlation_id' => 'corr-1',
        ]);

        self::assertTrue($success['success']);
        self::assertSame('sales', $success['inquiry_type']);
        self::assertSame('mail', $success['kind']);
        self::assertSame('', $success['failure_code']);
        self::assertSame('', $success['external_ticket_id']);
        self::assertNull($success['http_status']);
        self::assertSame('corr-1', $success['correlation_id']);

        $failure = map_dispatch_result('mail', false, ['inquiry_type' => 'sales']);
        self::assertFalse($failure['success']);
        self::assertSame('mail_rejected', $failure['failure_code']);
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $destination
     */
    #[DataProvider('singleDeficiencyProvider')]
    public function testSingleDeficiencyCodes(
        array $payload,
        array $destination,
        string $expectedCode
    ): void {
        $result = validate_inquiry_dispatch($payload, $destination);
        self::assertSame([$expectedCode], $result['deficiencies']);
    }

    /**
     * @return array<string, array{0: array<string, mixed>, 1: array<string, mixed>, 2: string}>
     */
    public static function singleDeficiencyProvider(): array
    {
        $basePayload = [
            'inquiry_type' => 'sales',
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'message' => 'Hi',
        ];
        $baseDestination = [
            'kind' => 'mail',
            'inquiry_type' => 'sales',
            'mail_to' => ['team@example.com'],
        ];

        return [
            'inquiry_type_invalid_empty' => [
                array_merge($basePayload, ['inquiry_type' => '']),
                array_merge($baseDestination, ['inquiry_type' => '']),
                'inquiry_type_invalid',
            ],
            'inquiry_type_invalid_format' => [
                array_merge($basePayload, ['inquiry_type' => 'BadSlug']),
                array_merge($baseDestination, ['inquiry_type' => 'BadSlug']),
                'inquiry_type_invalid',
            ],
            'name_empty' => [
                array_merge($basePayload, ['name' => '']),
                $baseDestination,
                'name_empty',
            ],
            'email_empty' => [
                array_merge($basePayload, ['email' => '']),
                $baseDestination,
                'email_empty',
            ],
            'email_invalid' => [
                array_merge($basePayload, ['email' => 'not-email']),
                $baseDestination,
                'email_invalid',
            ],
            'message_empty' => [
                array_merge($basePayload, ['message' => '']),
                $baseDestination,
                'message_empty',
            ],
            'destination_inquiry_type_mismatch' => [
                $basePayload,
                array_merge($baseDestination, ['inquiry_type' => 'recruit']),
                'destination_inquiry_type_mismatch',
            ],
            'destination_kind_unsupported' => [
                $basePayload,
                array_merge($baseDestination, ['kind' => 'slack']),
                'destination_kind_unsupported',
            ],
            'destination_empty' => [
                $basePayload,
                array_merge($baseDestination, ['mail_to' => []]),
                'destination_empty',
            ],
        ];
    }
}
