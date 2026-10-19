# S2J Inquiry Destination Service - 結果レコード

本ドキュメントは、1回の送信試行の **結果レコード** を定義します。プラグインがログや UI に使います。本ライブラリは **ログを書きません**。

## 個人情報の境界

[service_spec.md §個人情報の境界](../service_spec.md#個人情報の境界) が決定の正本です。

| 載せてよい (結果レコード) | 載せない |
| --- | --- |
| `success` (bool) | 氏名、`email`、`phone`、`message` |
| `inquiry_type` | API キー、`Authorization` |
| `kind` | 送信先が返した本文全体 |
| `external_ticket_id` (SaaS 時、空可) | `a***@example.com` 等の伏字 |
| `failure_code` (失敗時、空可) | |
| `http_status` (SaaS 時、任意) | |
| `correlation_id` (任意、非 PII) | |

失敗追跡は **相関 ID** (プラグイン生成) で足ります。`map_dispatch_result` の `context['correlation_id']` が非空なら、結果レコードに **同じ値をエコーする** (推奨)。空ならキーを省略するか空文字とする (実装で統一)。

## 結果レコードの形

```json
{
  "success": true,
  "inquiry_type": "sales",
  "kind": "mail",
  "external_ticket_id": "",
  "failure_code": "",
  "http_status": null,
  "correlation_id": "550e8400-e29b-41d4-a716-446655440000"
}
```

| フィールド | 説明 |
| --- | --- |
| success | Mail: `wp_mail === true`。SaaS: チケット ID 取得等 ([service_spec.md](../service_spec.md)) |
| failure_code | 初版: `mail_rejected` / `saas_http_error` / `saas_body_invalid` 等 (enumeration は contracts) |
| external_ticket_id | SaaS が返した ID。Mail 初版は常に空文字 |
| correlation_id | 任意。プラグイン生成 ID のエコー ([data_dictionary.md §correlation_id](../contracts/data_dictionary.md)) |

## map_dispatch_result

* Mail + `execution_outcome === true` → `success: true`, `failure_code: ""`。
* Mail + `false` → `success: false`, `failure_code: mail_rejected`。
* SaaS (将来): 2xx + パース済み ticket id → success。詳細は SaaS アダプタ追加時に core に spec を足す。

## 関連

* 契約: [../contracts/data_contract_spec.md](../contracts/data_contract_spec.md)
* dispatch: [dispatch_spec.md](./dispatch_spec.md)
