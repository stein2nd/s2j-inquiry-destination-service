# S2J Inquiry Destination Service - 宛先

本ドキュメントは、**1回の送信** に使う宛先レコードを定義します。種別 slug (`inquiry_type`) ごとにプラグインが **別レコード** を渡します (カタログはプラグイン — [data_dictionary.md §inquiry_type](../contracts/data_dictionary.md))。

## 責務

* 宛先レコードのフィールド意味を定義すること。
* Mail / SaaS 各 `kind` で必須となるフィールドを定義すること。

## 非責務

* 管理画面での宛先保存 UI。
* 「未設定タイプ」の選択肢や共通アドレスへの退避 (作らない — [service_spec.md](../service_spec.md))。
* 宛先レコードの正規化 (検証のみ — [validation_spec.md](./validation_spec.md))。

## 宛先レコード (全 kind 共通形)

プラグインは、未使用フィールドも **キーごと欠落させず** 渡します ([data_contract_spec.md](../contracts/data_contract_spec.md))。

| フィールド | 型 | Mail | saas_backlog | saas_asana |
| --- | --- | --- | --- | --- |
| kind | string | `mail` | `saas_backlog` | `saas_asana` |
| inquiry_type | string | 必須 (ペイロードと一致) | 同左 | 同左 |
| mail_to | list&lt;string&gt; | 必須 (有効1件以上) | 未使用 (空可) | 未使用 (空可) |
| saas_endpoint | string | 未使用 (空) | **必須** (スペース URL) | 省略可 (空 → [saas_asana_adapter_spec.md](./saas_asana_adapter_spec.md) のデフォルト) |
| saas_project_id | string | 未使用 (空) | **必須** (Backlog projectId) | **必須** (プロジェクト GID、1件) |
| credentials | object | 未使用 (`[]` 可) | 必須キーあり (下表) | 必須キーあり (下表) |

Backlog の課題種別 / 優先度 ID は **トップレベルには置かず**、プラグイン設定から `credentials` に載せます。

### Mail の To

* `mail_to` は1件以上の有効なメールアドレス文字列。
* 複数アドレスは **同じ1通** の To として材料に載せる。
* 空配列、または全要素が空 / 不正 → `destination_empty` ([validation_spec.md](./validation_spec.md))。

### 未設定タイプ

「共通宛先」は存在しません。同じ受信箱に集めたい場合は、各 `inquiry_type` に同じ `mail_to` を **明示的に** 設定します。

## Backlog (`kind === saas_backlog`)

| credentials キー | 説明 |
| --- | --- |
| `api_key` | ヘッダー `Backlog-API-Key` |
| `issue_type_id` | 課題種別 ID |
| `priority_id` | 優先度 ID |

詳細: [saas_backlog_adapter_spec.md](./saas_backlog_adapter_spec.md)。

## Asana (`kind === saas_asana`)

| credentials キー | 説明 |
| --- | --- |
| `access_token` | PAT (`Authorization: Bearer`) |

v0.3では `saas_project_id` **1件のみ** (複数プロジェクトは対象外)。

詳細: [saas_asana_adapter_spec.md](./saas_asana_adapter_spec.md)。

## プラグイン設定の想定

* サイトに1組: Backlog スペース URL + API キー、または Asana PAT (プラグインが読み、**実行時に** `credentials` に)。
* 種別ごと: `saas_project_id` (と Backlog の種別 / 優先度 ID を credentials に)。

## 関連

* 検証: [validation_spec.md](./validation_spec.md)
* Mail 材料: [mail_adapter_spec.md](./mail_adapter_spec.md)
