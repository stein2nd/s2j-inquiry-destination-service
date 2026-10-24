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

`inquiry_type` は **プラグイン定義の slug** です。JSON 例の `sales` は代表値にすぎません。Core は slug 形式のみ検証し、サイトに slug が登録されているかは見ません (v0.2.0以降)。

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

Mail / SaaS とも、キー `kind`, `inquiry_type`, `mail_to`, `saas_endpoint`, `saas_project_id`, `credentials` を **すべて** 含めます (検証は [validation_spec.md](../core/validation_spec.md))。

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

Backlog 例: `credentials` に `api_key`, `issue_type_id`, `priority_id`。Asana 例: `access_token` のみ (v0.3)。

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

Mail 例:

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

### DispatchMaterialResult (判別)

`destination.kind` が material 型の **正** とします (キー存在だけで推測しない)。

| kind 群 | material 必須キー |
| --- | --- |
| `mail` | `to`, `subject`, `body`, `content_type` |
| `saas_backlog`, `saas_asana` | `method`, `url`, `headers`, `body` |

## HttpRequestMaterial

SaaS アダプタの `build_dispatch_material` 出力 `material` 型です。

| フィールド | 型 | 説明 |
| --- | --- | --- |
| method | string | v0.3は `POST` のみ |
| url | string | 完全 URL (Backlog: クエリーに API キーを付けない) |
| headers | array&lt;string, string&gt; | ヘッダー名 → 値 |
| body | string | form-urlencoded **または** JSON の **生 string** (配列 DTO ではない) |

PHPUnit canonical 形は PHP 配列です。Fixture の API キーは `(runtime)` 等のプレースホルダ可とします。

```json
{
  "method": "POST",
  "url": "https://example.backlog.com/api/v2/issues",
  "headers": {
    "Backlog-API-Key": "(runtime)",
    "Content-Type": "application/x-www-form-urlencoded"
  },
  "body": "projectId=1&summary=..."
}
```

## SaasExecutionOutcome (map 入力)

| フィールド | 型 | 説明 |
| --- | --- | --- |
| http_status | int | 応答コード (通信失敗時 `0` 可) |
| parsed_body | array&lt;string, mixed&gt; | `json_decode(..., true)` の結果。失敗時は `[]` |

Backlog 成功例:

```json
{
  "http_status": 201,
  "parsed_body": { "id": 987654 }
}
```

Asana 成功例:

```json
{
  "http_status": 201,
  "parsed_body": {
    "data": { "gid": "1200000000000000", "name": "..." }
  }
}
```

写像: [result_spec.md](../core/result_spec.md)。

## 出力: ResultRecord

[../core/result_spec.md](../core/result_spec.md) 参照。`http_status`: SaaS 時 **int**、Mail 時 **null**。`external_ticket_id` は **常に string** (空文字可)。

## 関連

* 公開 API: [../interfaces/php_api_spec.md](../interfaces/php_api_spec.md)
