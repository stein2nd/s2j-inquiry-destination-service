<?php

declare(strict_types=1);

namespace S2J\InquiryDestinationService\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function S2J\InquiryDestinationService\build_dispatch_material;
use function S2J\InquiryDestinationService\map_dispatch_result;
use function S2J\InquiryDestinationService\validate_inquiry_dispatch;

final class SaasInquiryDestinationServiceTest extends TestCase
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
    private function validBacklogDestination(): array
    {
        return [
            'kind' => 'saas_backlog',
            'inquiry_type' => 'sales',
            'mail_to' => [],
            'saas_endpoint' => 'https://example.backlog.com',
            'saas_project_id' => '42',
            'credentials' => [
                'api_key' => 'key',
                'issue_type_id' => '1',
                'priority_id' => '2',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validAsanaDestination(): array
    {
        return [
            'kind' => 'saas_asana',
            'inquiry_type' => 'sales',
            'mail_to' => [],
            'saas_endpoint' => '',
            'saas_project_id' => '1200000000000000',
            'credentials' => [
                'access_token' => 'pat-token',
            ],
        ];
    }

    public function testUnsupportedKindDoesNotAddSaasDeficiencies(): void
    {
        $result = validate_inquiry_dispatch(
            $this->validPayload(),
            [
                'kind' => 'slack',
                'inquiry_type' => 'sales',
                'mail_to' => [],
                'saas_endpoint' => '',
                'saas_project_id' => '',
                'credentials' => [],
            ]
        );

        self::assertSame(['destination_kind_unsupported'], $result['deficiencies']);
    }

    public function testAsanaAllowsEmptySaasEndpoint(): void
    {
        $result = validate_inquiry_dispatch(
            $this->validPayload(),
            $this->validAsanaDestination()
        );

        self::assertSame([], $result['deficiencies']);
    }

    public function testBacklogCredentialsNotArrayAddsAllCredentialDeficiencies(): void
    {
        $destination = $this->validBacklogDestination();
        $destination['credentials'] = 'not-array';

        $result = validate_inquiry_dispatch($this->validPayload(), $destination);

        self::assertContains('destination_credentials_api_key_empty', $result['deficiencies']);
        self::assertContains('destination_credentials_issue_type_id_empty', $result['deficiencies']);
        self::assertContains('destination_credentials_priority_id_empty', $result['deficiencies']);
    }

    public function testBacklogSaasEndpointEmpty(): void
    {
        $destination = $this->validBacklogDestination();
        $destination['saas_endpoint'] = '';

        $result = validate_inquiry_dispatch($this->validPayload(), $destination);
        self::assertSame(['destination_saas_endpoint_empty'], $result['deficiencies']);
    }

    public function testBacklogSaasProjectIdEmpty(): void
    {
        $destination = $this->validBacklogDestination();
        $destination['saas_project_id'] = '';

        $result = validate_inquiry_dispatch($this->validPayload(), $destination);
        self::assertSame(['destination_saas_project_id_empty'], $result['deficiencies']);
    }

    public function testAsanaSaasProjectIdEmpty(): void
    {
        $destination = $this->validAsanaDestination();
        $destination['saas_project_id'] = '';

        $result = validate_inquiry_dispatch($this->validPayload(), $destination);
        self::assertSame(['destination_saas_project_id_empty'], $result['deficiencies']);
    }

    #[DataProvider('saasSingleDeficiencyProvider')]
    public function testSaasSingleDeficiencyCodes(
        string $base,
        callable $destinationMutator,
        string $expectedCode
    ): void {
        $destination = $base === 'asana'
            ? $this->validAsanaDestination()
            : $this->validBacklogDestination();
        $destinationMutator($destination);

        $result = validate_inquiry_dispatch($this->validPayload(), $destination);
        self::assertSame([$expectedCode], $result['deficiencies']);
    }

    /**
     * @return array<string, array{0: string, 1: callable, 2: string}>
     */
    public static function saasSingleDeficiencyProvider(): array
    {
        return [
            'credentials_api_key_empty' => [
                'backlog',
                static function (array &$destination): void {
                    $destination['credentials']['api_key'] = '';
                },
                'destination_credentials_api_key_empty',
            ],
            'credentials_issue_type_id_empty' => [
                'backlog',
                static function (array &$destination): void {
                    $destination['credentials']['issue_type_id'] = '';
                },
                'destination_credentials_issue_type_id_empty',
            ],
            'credentials_priority_id_empty' => [
                'backlog',
                static function (array &$destination): void {
                    $destination['credentials']['priority_id'] = '';
                },
                'destination_credentials_priority_id_empty',
            ],
            'credentials_access_token_empty' => [
                'asana',
                static function (array &$destination): void {
                    $destination['credentials']['access_token'] = '';
                },
                'destination_credentials_access_token_empty',
            ],
        ];
    }

    public function testBacklogBuildMaterialMatchesMailPlainText(): void
    {
        $validated = validate_inquiry_dispatch(
            $this->validPayload(),
            $this->validBacklogDestination()
        );
        $built = build_dispatch_material($validated['payload'], $validated['destination']);
        /** @var array<string, mixed> $material */
        $material = $built['material'];

        self::assertSame('POST', $material['method']);
        self::assertSame(
            'https://example.backlog.com/api/v2/issues',
            $material['url']
        );
        self::assertSame('key', $material['headers']['Backlog-API-Key']);

        parse_str($material['body'], $form);
        self::assertSame('42', $form['projectId'] ?? '');
        self::assertSame('1', $form['issueTypeId'] ?? '');
        self::assertSame('2', $form['priorityId'] ?? '');
        self::assertSame('[sales] Product question', $form['summary'] ?? '');
        self::assertSame(
            "sales\n\nJane Doe\n\njane@example.com\n\nHello",
            $form['description'] ?? ''
        );
    }

    public function testAsanaBuildMaterialJsonDataWrapper(): void
    {
        $validated = validate_inquiry_dispatch(
            $this->validPayload(),
            $this->validAsanaDestination()
        );
        $built = build_dispatch_material($validated['payload'], $validated['destination']);
        /** @var array<string, mixed> $material */
        $material = $built['material'];

        self::assertSame('POST', $material['method']);
        self::assertSame('https://app.asana.com/api/1.0/tasks', $material['url']);
        self::assertSame('Bearer pat-token', $material['headers']['Authorization']);

        /** @var array{data?: array<string, mixed>} $decoded */
        $decoded = json_decode($material['body'], true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($decoded['data'] ?? null);
        self::assertSame('[sales] Product question', $decoded['data']['name'] ?? '');
        self::assertSame(['1200000000000000'], $decoded['data']['projects'] ?? null);
        self::assertFalse($decoded['data']['completed'] ?? true);
    }

    public function testMapBacklogSuccessNumericId(): void
    {
        $result = map_dispatch_result('saas_backlog', [
            'http_status' => 201,
            'parsed_body' => ['id' => 1],
        ], ['inquiry_type' => 'sales']);

        self::assertTrue($result['success']);
        self::assertSame('1', $result['external_ticket_id']);
        self::assertSame(201, $result['http_status']);
        self::assertSame('', $result['failure_code']);
    }

    public function testMapBacklogHttpErrors(): void
    {
        foreach ([400, 0] as $status) {
            $result = map_dispatch_result('saas_backlog', [
                'http_status' => $status,
                'parsed_body' => [],
            ], ['inquiry_type' => 'sales']);

            self::assertFalse($result['success']);
            self::assertSame('saas_http_error', $result['failure_code']);
        }
    }

    public function testMapBacklogBodyInvalid(): void
    {
        $missingId = map_dispatch_result('saas_backlog', [
            'http_status' => 201,
            'parsed_body' => [],
        ], ['inquiry_type' => 'sales']);
        self::assertSame('saas_body_invalid', $missingId['failure_code']);

        $wrong2xx = map_dispatch_result('saas_backlog', [
            'http_status' => 200,
            'parsed_body' => ['id' => 9],
        ], ['inquiry_type' => 'sales']);
        self::assertSame('saas_body_invalid', $wrong2xx['failure_code']);
    }

    public function testMapAsanaSuccess(): void
    {
        $result = map_dispatch_result('saas_asana', [
            'http_status' => 201,
            'parsed_body' => ['data' => ['gid' => '123']],
        ], ['inquiry_type' => 'sales', 'correlation_id' => 'c-1']);

        self::assertTrue($result['success']);
        self::assertSame('123', $result['external_ticket_id']);
        self::assertSame('c-1', $result['correlation_id']);
        self::assertSame('saas_asana', $result['kind']);
        self::assertSame(201, $result['http_status']);
    }

    public function testMapBacklogFailureEchoesHttpStatus(): void
    {
        $result = map_dispatch_result('saas_backlog', [
            'http_status' => 503,
            'parsed_body' => [],
        ], ['inquiry_type' => 'sales']);

        self::assertSame(503, $result['http_status']);
        self::assertSame('saas_backlog', $result['kind']);
    }

    public function testMapSaasThrowsWhenOutcomeInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        map_dispatch_result('saas_backlog', true, ['inquiry_type' => 'sales']);
    }

    public function testMapSaasThrowsWhenKeysMissing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        map_dispatch_result('saas_backlog', ['http_status' => 201], ['inquiry_type' => 'sales']);
    }
}
