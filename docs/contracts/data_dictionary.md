# S2J Inquiry Destination Service - データ辞書

## inquiry_type

**種別カタログ (増減・表示名・宛先) の正本は S2J Inquiry Destination プラグイン** です。本ライブラリは slug **形式** のみ検証します ([payload_spec.md](../core/payload_spec.md))。

| 項目 | 説明 |
| --- | --- |
| 値 | 小文字 slug (例: `sales`, `recruit`, `support`) |
| 追加・削除 | プラグイン管理画面 (本 Service の minor リリース不要) |
| 意味・ラベル | プラグイン。件名は `subject_hint` に載せる |

### よく使う例 (プラグイン初期データ)

| 値 | 意味 (参考) |
| --- | --- |
| `sales` | 営業問い合わせ |
| `recruit` | 採用問い合わせ |

### プラグイン側の拡張手順

1. 管理画面で slug ・表示名・`mail_to` を登録する。
2. フォーム ID (等) を slug に割り当てる。
3. 送信時に `subject_hint` に表示名 (i18n) を渡す。

### Service 側 (slug 形式変更時)

slug 規則そのものを変える場合は **major** (SemVer)。個別 slug の追加は Service 変更なし。

## destination.kind

### 命名規則

* 問い合わせ SaaS REST: `saas_{product}` (例: `saas_backlog`、`saas_asana`)。**実装と同時** に辞書と dispatch レジストリに追加する。空 stub 禁止。
* チャット通知 (Slack / LINE WORKS 等): 初版スコープ外。採用する場合も上記と同様に `kind` を辞書で先に定義してから実装する。

| 値 | 状態 |
| --- | --- |
| `mail` | 実装済 |
| `saas_backlog` | 仕様確定 (Backlog 課題追加) — 実装は v0.3.0 |
| `saas_asana` | 仕様確定 (Asana タスク作成) — 実装は v0.3.0 |

## deficiencies

[../core/validation_spec.md §不足コード — 評価順](../core/validation_spec.md) が正本です (文字列 slug、スネークケース)。

v0.3追記:

* `destination_saas_endpoint_empty`
* `destination_saas_project_id_empty`
* `destination_credentials_api_key_empty`
* `destination_credentials_issue_type_id_empty`
* `destination_credentials_priority_id_empty`
* `destination_credentials_access_token_empty`

## failure_code (結果)

| コード | 意味 |
| --- | --- |
| `mail_rejected` | `wp_mail` が false |
| `saas_http_error` | `http_status` が `200`–`299` 以外 ( **`0` 含む** ) |
| `saas_body_invalid` | `200`–`299` だが v0.3成功条件 ( **201 + ticket id** ) を満たさない |

## correlation_id

| 項目 | 説明 |
| --- | --- |
| 生成 | プラグイン (1送信試行ごと) |
| 用途 | 失敗追跡。ログと結果レコードの突合 (PII ではない) |
| 結果レコード | 任意。`map_dispatch_result` の `context['correlation_id']` を写像時に **エコー推奨** ([result_spec.md](../core/result_spec.md)) |

## 用語

| 用語 | 説明 |
| --- | --- |
| 統一ペイロード | SMF 送信をプラグインが翻訳した後の、本ライブラリ入力 |
| 種別 (`inquiry_type`) | フォーム種別 slug。本文では **種別** と `inquiry_type` に統一する |
| MailMaterial | `to` / `subject` / `body` / `content_type` のみ (From / Transport 系キーは含めない — [transport_boundary_spec.md](../core/transport_boundary_spec.md)) |
| Transport 層 | SMTP、`wp_mail` 経路、Email Auth (DKIM / SPF / DMARC)、到達性。Service 非目標 |
| プラグイン Runner | `build_dispatch_material` 後に `wp_mail` / HTTP を実行する層 |
| HttpRequestMaterial | method / url / headers / body (SaaS 材料) |
| SaasExecutionOutcome | `map_dispatch_result` 第2引数 (SaaS 時): `http_status` + `parsed_body` |
| external_ticket_id | SaaS 起票 ID (Backlog `id`, Asana task `gid`)。**string** |
| 材料 (material) | MailMaterial または HttpRequestMaterial |
| 送信ポート | 渡された宛先の `kind` からアダプタ関数を選ぶ層 (タイプ解決はプラグイン) |

## 関連

* 契約: [data_contract_spec.md](./data_contract_spec.md)
