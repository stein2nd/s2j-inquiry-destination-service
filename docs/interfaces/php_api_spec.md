# S2J Inquiry Destination Service - PHP 公開面

## パッケージ

公開関数は `composer.json` の **`autoload.files`** (例: `src/functions.php`) で載せます。`Core\*` は PSR-4内部実装です。

| 項目 | 値 |
| --- | --- |
| Composer 名 | `s2j/inquiry-destination-service` |
| 名前空間 | `S2J\InquiryDestinationService\` |
| PHP | [build_and_release.md §PHP 要件](../engineering/build_and_release.md#php-要件-本ドキュメントが正本) |

## 公開 API (初版)

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

`destination.kind` が `mail` のとき `material` は MailMaterial です ([../core/mail_adapter_spec.md](../core/mail_adapter_spec.md))。

**deficiencies 非空の destination / payload で呼んだ場合** は、`InvalidArgumentException` (プログラマー誤り)。

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
 * @param string $kind mail 等
 * @param mixed $execution_outcome Mail: bool。将来 SaaS: array
 * @param array<string, mixed> $context inquiry_type (推奨), correlation_id (任意・非 PII、結果にエコー推奨) 等
 * @return array<string, mixed> ResultRecord
 */
function map_dispatch_result(string $kind, mixed $execution_outcome, array $context = []): array;
```

## 非公開

* `Core\Adapters\*` の内部ヘルパー
* 件名テンプレート定数 (文言変更は patch/minor。成功条件が変わる場合 major)

## バージョニング

* 不足 / failure コードの **削除・意味変更** → major
* コード **追加** → minor
* ペイロード必須フィールド **追加** → major。任意フィールド追加 → minor

## 関連

* 使用方法: [usage_spec.md](./usage_spec.md)
* 契約: [../contracts/data_contract_spec.md](../contracts/data_contract_spec.md)
