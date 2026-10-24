# S2J Inquiry Destination Service - Asana SaaS アダプタ

**kind:** `saas_asana`

## 責務

* 統一ペイロード + 宛先から、Asana **タスク作成** 向け HttpRequestMaterial を組む。
* 成功時の task gid は [result_spec.md](./result_spec.md) (`parsed_body['data']['gid']`)。

## 非責務

* HTTP 送信、OAuth フロー、トークン保存
* セクション移動・カスタムフィールド (v0.3は `completed: false` のみ)

## 認証 (v0.3)

OAuth2.0 (`tasks:write`) は、v0.3対象外とします。

| 方式 | 材料 |
| --- | --- |
| PAT | ヘッダー `Authorization: Bearer {access_token}` ← `destination.credentials['access_token']` |

## 宛先

[destination_spec.md](./destination_spec.md) の Asana 行どおり。`saas_project_id` は **1GID** のみ。

## 件名・本文

Backlog と同じく [Mail.php](../../src/Core/Adapters/Mail.php) の件名・本文規則 ([saas_backlog_adapter_spec.md §件名・本文](./saas_backlog_adapter_spec.md))。

| Asana `data` 内 | 値 |
| --- | --- |
| `name` | 件名 (summary 同型) |
| `notes` | 本文 (description 同型) |
| `projects` | `[ destination.saas_project_id ]` (string GID1件) |
| `completed` | `false` |

## HttpRequestMaterial

* `method`: `POST`
* `url`: `{base}/api/1.0/tasks`  
  * `base = rtrim(saas_endpoint, '/')` が空なら `https://app.asana.com`
* `headers`: `Authorization`, `Content-Type: application/json`
* `body`: JSON **1本 string**。トップレベルに Asana 必須の `data` ラッパ:

```json
{
  "data": {
    "name": "[sales] 営業",
    "notes": "sales\n\n...",
    "projects": ["1200000000000000"],
    "completed": false
  }
}
```

(`projects` は JSON 配列。PHP 実装では `json_encode` で `data` オブジェクトごと渡す)

## 写像

[result_spec.md §SaaS 写像アルゴリズム](./result_spec.md) — gid は `parsed_body['data']['gid']`。

## 関連

* [saas_backlog_adapter_spec.md](./saas_backlog_adapter_spec.md)
* [dispatch_spec.md](./dispatch_spec.md)
