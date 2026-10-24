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
| `http_status` (SaaS 時) | |
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
| success | Mail: `wp_mail === true`。SaaS: 下記アルゴリズム |
| failure_code | `mail_rejected` / `saas_http_error` / `saas_body_invalid` ([data_dictionary.md](../contracts/data_dictionary.md)) |
| external_ticket_id | SaaS 起票 ID。**常に string** (空可)。Mail は常に空文字 |
| correlation_id | 任意。プラグイン生成 ID のエコー |
| http_status | SaaS 時 **int**。Mail 時 **null** |

## map_dispatch_result — 共通

* 結果の `kind` は、関数第1引数 `$kind` と **同一 string**。
* `inquiry_type` は `$context['inquiry_type']` を trim して載せる (**空でもキーは出す** — Mail 実装同型)。

### Mail

* `execution_outcome === true` → `success: true`, `failure_code: ""`, `http_status: null`。
* `false` → `success: false`, `failure_code: mail_rejected`。

### SaaS — 入力 SaasExecutionOutcome

[contracts/data_contract_spec.md](../contracts/data_contract_spec.md) 参照。

* `$execution_outcome` が **array でない**、または `http_status` / `parsed_body` キー欠落 → `InvalidArgumentException` (プログラマー誤り)。
* `parsed_body` は **array** (プラグインが decode 失敗時 `[]` を渡す)。

### SaaS — 写像アルゴリズム (実装の正)

`ticket_id` は kind ごとに次の **非空 string** を取り出せた場合だけセット (数値 `id` は `(string)` 化):

| kind | 抽出 |
| --- | --- |
| `saas_backlog` | `parsed_body['id']` が int または非空 string |
| `saas_asana` | `parsed_body['data']['gid']` が非空 string |

```
http_status = (int) execution_outcome['http_status']
parsed_body = execution_outcome['parsed_body']  // array

ticket_id = (上表に従い string 化)

if http_status === 201 && ticket_id !== '':
  success = true, failure_code = '', external_ticket_id = ticket_id
elseif http_status < 200 || http_status > 299:
  success = false, failure_code = saas_http_error, external_ticket_id = ''
else:
  success = false, failure_code = saas_body_invalid, external_ticket_id = ''
```

* 通信エラー等で `http_status === 0` → `saas_http_error`。
* **`201` 以外の `2xx`** (例: 200) で body に id があっても → `saas_body_invalid` (v0.3は作成 API の **201のみ** 成功)。

製品別 API フィールド名: [saas_backlog_adapter_spec.md](./saas_backlog_adapter_spec.md) / [saas_asana_adapter_spec.md](./saas_asana_adapter_spec.md)。

## 結果レコード例 (SaaS 成功)

```json
{
  "success": true,
  "inquiry_type": "sales",
  "kind": "saas_backlog",
  "external_ticket_id": "987654",
  "failure_code": "",
  "http_status": 201,
  "correlation_id": "550e8400-e29b-41d4-a716-446655440000"
}
```

## 関連

* 契約: [../contracts/data_contract_spec.md](../contracts/data_contract_spec.md)
* dispatch: [dispatch_spec.md](./dispatch_spec.md)
