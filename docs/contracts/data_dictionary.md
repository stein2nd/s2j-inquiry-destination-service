# S2J Inquiry Destination Service - データ辞書

## inquiry_type

追加は **minor** (SemVer) とします。

削除・意味変更は **major** とします。

| 値 | 意味 |
| --- | --- |
| `sales` | 営業問い合わせ |
| `recruit` | 採用問い合わせ |

### 拡張手順

1. 本表に値と意味を追記する (minor)。
2. [payload_spec.md](../core/payload_spec.md) の列挙定数 (実装) を更新する。
3. [testing_spec.md](../testing_spec.md) に `inquiry_type_invalid` 以外の代表入力を1件足す。
4. プラグイン側のフォーム ID 対応 UI を更新する (別 repo)。

## destination.kind

### 命名規則

* 初版: `mail` のみ実装する。
* 問い合わせ SaaS REST: `saas_{product}` (例: `saas_backlog`、`saas_asana`)。製品確定後に **実装と同時** に辞書と dispatch レジストリに追加する。空 stub 禁止。
* チャット通知 (Slack / LINE WORKS 等): 初版スコープ外。採用する場合も上記と同様に `kind` を辞書で先に定義してから実装する。

| 値 | 初版 |
| --- | --- |
| `mail` | 実装あり |

## deficiencies

[../core/validation_spec.md](../core/validation_spec.md) のコード列です。文字列 slug。スネークケース。

## failure_code (結果)

| コード | 意味 |
| --- | --- |
| `mail_rejected` | `wp_mail` が false |
| `saas_http_error` | 将来: 非 `2xx` |
| `saas_body_invalid` | 将来: JSON / チケット ID 欠落 |

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
| 種別 (`inquiry_type`) | フォーム種別 (営業 / 採用)。本文では **種別** と `inquiry_type` に統一する |
| 材料 (material) | `wp_mail` 引数または HTTP リクエストの中身 |
| 送信ポート | 渡された宛先の `kind` からアダプタ関数を選ぶ層 (タイプ解決はプラグイン) |

## 関連

* 契約: [data_contract_spec.md](./data_contract_spec.md)
