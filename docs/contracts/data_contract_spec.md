# S2J Inquiry Destination Service - 入出力仕様

本ドキュメントは、本ライブラリの **入出力データ契約 (DTO)** を定義します。PHP 公開面と Core の間で共有する Source of Truth とします。

## 概要

* 永続化形式はプラグインが決める。本契約は **1回の送信試行** の入出力である。
* 検証規則の本体は Core ([../core/validation_spec.md](../core/validation_spec.md))。本書は形である。
* OpenAPI は初版では使わない (Survey Service 同型)。

### PHP と JSON の対応

* PHP 実装では DTO は **`array<string, mixed>`** (連想配列) である。
* 本書の JSON 例は可読性のため `{}` を使う。`meta` / `credentials` は **空なら正規化後 `[]`** ([payload_spec.md](../core/payload_spec.md))。JSON Fixture (`tests/Fixtures/`) は `{}` で書いても、正規化後に `[]` と一致すればよい。
* **canonical 形** (PHPUnit スナップショットの正): 正規化・検証後の PHP 配列。空オブジェクト相当フィールドは `[]`。

## 入力: InquiryPayload

意味の詳細は [../core/payload_spec.md](../core/payload_spec.md) と [data_dictionary.md](./data_dictionary.md) をご覧ください。

```json
{
  "inquiry_type": "sales",
  "name": "string",
  "email": "user@example.com",
  "phone": "",
  "message": "string",
  "subject_hint": "",
  "locale": "ja",
  "meta": {}
}
```

## 入力: DestinationRecord

初版 Mail では `mail_to` のみ使用します。SaaS フィールドは、空文字 / 空オブジェクト可とします。

```json
{
  "kind": "mail",
  "inquiry_type": "sales",
  "mail_to": ["team@example.com"],
  "saas_endpoint": "",
  "saas_project_id": "",
  "credentials": {}
}
```

## 出力: ValidateInquiryDispatchResult

```json
{
  "payload": { },
  "destination": { },
  "deficiencies": []
}
```

## 出力: DispatchMaterialResult

`material` 欠落時は `deficiencies` 非空とします (通常は validate 後のみ build を呼ぶ)。

```json
{
  "material": {
    "to": ["team@example.com"],
    "subject": "[sales] Inquiry",
    "body": "...",
    "content_type": "text/plain"
  },
  "deficiencies": []
}
```

## 出力: ResultRecord

[../core/result_spec.md](../core/result_spec.md) 参照。`correlation_id` は任意 (プラグインが `map_dispatch_result` の context で渡し、写像時にエコー推奨)。

## 将来: HttpRequestMaterial (SaaS)

SaaS アダプタ追加時に本節を追記します。

| フィールド | 説明 |
| --- | --- |
| method | `POST` 等 |
| url | 完全 URL (プラグインが credentials と合成済みでも可) |
| headers | 連想配列。`Authorization` は材料に含めてよいが **結果・ログ規則はプラグイン** |
| body | JSON 文字列 |

## 関連

* 公開 API: [../interfaces/php_api_spec.md](../interfaces/php_api_spec.md)
