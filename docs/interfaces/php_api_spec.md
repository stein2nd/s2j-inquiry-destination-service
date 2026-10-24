# S2J Inquiry Destination Service - PHP 公開面

## パッケージ

公開関数は `composer.json` の **`autoload.files`** (例: `src/functions.php`) で載せます。`Core\*` は PSR-4内部実装です。

| 項目 | 値 |
| --- | --- |
| Composer 名 | `s2j/inquiry-destination-service` |
| 名前空間 | `S2J\InquiryDestinationService\` |
| PHP | [build_and_release.md §PHP 要件](../engineering/build_and_release.md#php-要件-本ドキュメントが正本) |

## 公開 API

### normalize_inquiry_payload

規則: [../core/payload_spec.md](../core/payload_spec.md)

```php
/**
 * @param array<string, mixed> $payload
 * @return array<string, mixed> 正規化後ペイロード
 */
function normalize_inquiry_payload(array $payload): array;
```

### validate_inquiry_dispatch

内部で正規化してから、検証してかまいません。規則: [../core/validation_spec.md](../core/validation_spec.md)

```php
/**
 * @param array<string, mixed> $payload
 * @param array<string, mixed> $destination
 * @return array{
 *   payload: array<string, mixed>,
 *   destination: array<string, mixed>,
 *   deficiencies: list<string>
 * }
 */
function validate_inquiry_dispatch(array $payload, array $destination): array;
```

### build_dispatch_material

`destination.kind` により `material` の形が分岐 ([data_contract_spec.md §DispatchMaterialResult](../contracts/data_contract_spec.md))。

**deficiencies 非空の destination / payload で呼んだ場合** は、`InvalidArgumentException` (プログラマー誤り)。

| kind | material |
| --- | --- |
| `mail` | MailMaterial |
| `saas_backlog` / `saas_asana` | HttpRequestMaterial |

```php
/**
 * @param array<string, mixed> $payload 正規化済み
 * @param array<string, mixed> $destination 検証済み (deficiencies 空)
 * @return array{material: array<string, mixed>|null, deficiencies: list<string>}
 */
function build_dispatch_material(array $payload, array $destination): array;
```

### map_dispatch_result

規則: [../core/result_spec.md](../core/result_spec.md)

```php
/**
 * @param string $kind mail | saas_backlog | saas_asana
 * @param mixed $execution_outcome Mail: bool。SaaS: SaasExecutionOutcome
 * @param array<string, mixed> $context inquiry_type (結果に載せる), correlation_id (任意)
 * @return array<string, mixed> ResultRecord
 */
function map_dispatch_result(string $kind, mixed $execution_outcome, array $context = []): array;
```

| kind | execution_outcome |
| --- | --- |
| `mail` | `bool` |
| `saas_backlog` / `saas_asana` | `array{http_status: int, parsed_body: array<string, mixed>}` |

* 未登録 `kind` → `InvalidArgumentException`。
* SaaS で array 形不正 (必須キー欠落) → `InvalidArgumentException`。
* HTTP 応答の成否は outcome 内の `http_status` / `parsed_body` から **failure_code** を決める。

## 非公開

* `Core\Adapters\*` の内部ヘルパー
* 件名組立 (Mail アダプタ内部。`subject_hint` / slug フォールバック — [mail_adapter_spec.md](../core/mail_adapter_spec.md))

## バージョニング

* 不足 / failure コードの **削除・意味変更** → major
* コード **追加** → minor
* ペイロード必須フィールド **追加** → major。任意フィールド追加 → minor
* `inquiry_type` の **slug 形式規則の変更**、または Core 列挙の復活・拒否範囲の変更 → major (v0.2.0で列挙廃止 — [data_dictionary.md §inquiry_type](../contracts/data_dictionary.md))
* `saas_*` kind 追加 → minor (0.3.0)

## 関連

* 使用方法: [usage_spec.md](./usage_spec.md)
* 契約: [../contracts/data_contract_spec.md](../contracts/data_contract_spec.md)
