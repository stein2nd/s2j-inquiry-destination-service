# S2J Inquiry Destination Service - 宛先

本ドキュメントは、**1回の送信** に使う宛先レコードを定義します。種別 (`sales` / `recruit`) ごとにプラグインが **別レコード** を渡します。

## 責務

* 宛先レコードのフィールド意味を定義すること。
* Mail 初版の To 一覧の扱いを定義すること。

## 非責務

* 管理画面での宛先保存 UI。
* 「未設定タイプ」の選択肢や共通アドレスへの退避 (作らない — [service_spec.md](../service_spec.md))。
* 宛先レコードの正規化 (検証のみ — [validation_spec.md](./validation_spec.md))。

## 宛先レコード

| フィールド | 型 | 説明 |
| --- | --- | --- |
| kind | string | 初版は `mail` のみ。将来 `saas_backlog` 等を追加 |
| inquiry_type | string | この宛先が適用される種別。ペイロードと一致させる |
| mail_to | list&lt;string&gt; | **Mail 時必須**。同一通の To として渡すアドレス列。CC/BCC は持たない |
| saas_endpoint | string | 将来 SaaS 用。サイトに1組の URL (プラグインが解決して渡す) |
| saas_project_id | string | 将来 SaaS 用。タイプごとのキュー / プロジェクト ID |
| credentials | object | 実行時のみ。API キー等。**本ライブラリは永続化しない** |

### Mail の To

* `mail_to` は1件以上の有効なメールアドレス文字列。
* 複数アドレスは **同じ1通** の To として材料に載せる (メーリングリスト / 共有受信箱1アドレスを推奨 — 文言はプラグイン UI)。
* 空配列、または全要素が空 / 不正 → 送信しない。不足 `destination_empty` ([validation_spec.md](./validation_spec.md))。

### 未設定タイプ

「共通宛先」は存在しません。同じ受信箱に集めたい場合は、各 `inquiry_type` に同じ `mail_to` を **明示的に** 設定します。

## 関連

* 検証: [validation_spec.md](./validation_spec.md)
* Mail 材料: [mail_adapter_spec.md](./mail_adapter_spec.md)
