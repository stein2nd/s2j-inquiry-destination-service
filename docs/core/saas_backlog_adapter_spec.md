# S2J Inquiry Destination Service - Backlog SaaS アダプタ

**kind:** `saas_backlog`

## 責務

* 統一ペイロード + 宛先から、Backlog **課題追加** `POST /api/v2/issues` 向け [HttpRequestMaterial](../contracts/data_contract_spec.md) を組む。
* 成功時の `id` フィールド意味を [result_spec.md](./result_spec.md) に委譲する (写像ロジックの正本は result_spec)。

## 非責務

* HTTP 送信、OAuth、API キーの保存
* 課題一覧・コメント・添付
* URL クエリー `apiKey=` (**禁止** — ヘッダーのみ)

## 認証 (v0.3)

OAuth2.0は、v0.3対象外とします。

| 方式 | 材料 |
| --- | --- |
| API キー | ヘッダー `Backlog-API-Key: {api_key}` ← `destination.credentials['api_key']` |

## 宛先

[destination_spec.md](./destination_spec.md) の Backlog 行どおりです。

## 件名・本文 (Mail と同一規則)

実装は [Mail.php](../../src/Core/Adapters/Mail.php) と **同一ロジック** にします (件名・プレーン本文。実装 PR では共有関数に抽出可)。

| 用途 | 規則 |
| --- | --- |
| 件名 (`summary`) | `sprintf('[%s] %s', inquiry_type, subject_hint 非空 ? subject_hint : inquiry_type)` |
| 本文 (`description`) | Mail `buildBody` 同型 (種別 → 氏名 → メール → 電話 (任意) → message、`\\n\\n` 区切り) |

## ペイロード → Backlog form パラメータ

`Content-Type: application/x-www-form-urlencoded`。キー名は Backlog API **camelCase**。

| パラメータ | 値 |
| --- | --- |
| `projectId` | `destination.saas_project_id` (string) |
| `summary` | 上表の件名 |
| `description` | 上表の本文 |
| `issueTypeId` | `credentials.issue_type_id` (string) |
| `priorityId` | `credentials.priority_id` (string) |

## HttpRequestMaterial

* `method`: `POST`
* `url`: `{rtrim(saas_endpoint, '/')}/api/v2/issues` (クエリーに apiKey を付けない)
* `headers`: `Backlog-API-Key`, `Content-Type: application/x-www-form-urlencoded`
* `body`: PHP `http_build_query` 相当の1本 string

## 写像

[result_spec.md §SaaS 写像アルゴリズム](./result_spec.md) — チケット ID は JSON トップレベル `id`。

## 関連

* dispatch: [dispatch_spec.md](./dispatch_spec.md)
* Mail 参照: [mail_adapter_spec.md](./mail_adapter_spec.md)
