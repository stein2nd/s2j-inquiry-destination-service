# S2J Inquiry Destination Service - 概要

## 概要

**S2J Inquiry Destination Service** は、問い合わせフォーム送信を **統一ペイロード** に正規化し、種別 (`inquiry_type`) ごとの送信先 (Mail / Backlog / Asana) 向けに **メール文面または HTTP リクエスト材料** を組み立てる **WordPress 非依存** の Composer ライブラリです。

Similarity Service や Webinar Survey Service と同型に、計算だけをパッケージ化し、副作用 (保存、フック、`wp_mail`、HTTP) は [S2J Inquiry Destination](https://github.com/stein2nd/s2j-inquiry-destination) プラグインが担います。

## 目的

* **種別 slug (`inquiry_type`) ごと** に異なる宛先に、同じペイロード形で送れるようにする (種別の増減・表示名・`mail_to` はプラグイン、Core は slug 形式のみ検証)。
* MW WP Form 依存や WP DB への案件蓄積を避け、将来は問い合わせ SaaS REST に差し替え可能にする。
* PHPUnit のみで送信組立ロジックを回帰できるようにする。

## 非目的 (初版)

* フォーム HTML、Snow Monkey Forms フック、管理画面 UI。
* `wp_mail` / HTTP の実行、OAuth、API キーの永続化。
* 再送キュー、失敗本文の DB キャッシュ。
* チケットステータス画面、WP 上の案件一覧。
* 公開日・更新日・ピン留め (別サービス)。

## 責務

| 責務 | 内容 |
| --- | --- |
| 正規化 | 種別、氏名、連絡先、本文などを、比較可能なレコードにそろえる |
| 検証 | 必須フィールド、宛先未設定などを、**不足コード** で返す |
| 送信ポート | 宛先レコードの `kind` (`mail` 等) から、アダプタ関数を選ぶ |
| アダプタ | Mail 文面、Backlog / Asana 向け HttpRequestMaterial (材料のみ) |
| 結果写像 | 実行結果 (Mail は bool、SaaS は `http_status` + パース済み JSON) を **個人情報を含まない** 結果レコードに写す |

## 非責務

* API キー・エンドポイントの読み順 (環境変数 → 定数 → オプション):
    * プラグイン。
* フォーム ID → 種別の解決:
    * プラグイン。
* ログ出力 (`error_log`):
    * プラグイン。本ライブラリはログを書かない。
* 自動返信:
    * プラグイン。成功条件に含めない。
* 送信失敗時の入力欄復元:
    * プラグイン。

## 関連

* 統合見取り図: [service_spec.md](./service_spec.md)
* 索引: [specs.md](./specs.md)
