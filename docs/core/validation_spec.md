# S2J Inquiry Destination Service - 検証

本ドキュメントは、送信前検証と **不足コード (deficiencies)** を定義します。

## 責務

* 不足コードの enumeration と安定順序を定義すること。
* 宛先の未設定時に **送らない** ことを定義すること。

## 非責務

* プラグイン UI のエラーメッセージ (i18n はプラグイン)。
* 種別 slug がサイト設定に存在するか (プラグインが保存時・送信前に担保)。

## 検証の流れ

1. `normalize_inquiry_payload` を適用 (公開 API 内で呼んでもよい)。
2. ペイロード項目を検証。
3. 宛先レコードを **正規化せず** 検証のみ (`inquiry_type` 一致、`kind` 登録済み、kind 別必須フィールド)。未知キーの除去や型 coerce は行わない。形の正しさはプラグインが [data_contract_spec.md](../contracts/data_contract_spec.md) に合わせて渡す。
4. 戻り: `{ payload, destination, deficiencies: list<string> }`。`payload` は正規化済み。`destination` は入力をそのまま返す (deficiencies 空の場合)。

**deficiencies が1件でもあれば、材料組立は行いません** (Webinar Service の build 前 validate と同型)。

## 不足コード — 評価順

`Validate::deficiencies` は、**上から順に** 条件を見て、該当するコードを **append** します。kind 専用行は、記載の `kind` の場合だけ評価します。

`destination_kind_unsupported` の場合は、**以降の kind 専用行 (Mail / SaaS) は評価しません**。

| 順 | コード | 条件 |
| --- | --- | --- |
| 1 | `inquiry_type_invalid` | `inquiry_type` が空、または [payload_spec.md](./payload_spec.md) の slug 形式非準拠 |
| 2 | `name_empty` | `name` が空 |
| 3 | `email_empty` | `email` が空 |
| 4 | `email_invalid` | `email` が簡易 RFC 非準拠 (実装は filter_var 等) |
| 5 | `message_empty` | `message` が空 |
| 6 | `destination_inquiry_type_mismatch` | 宛先の `inquiry_type` ≠ ペイロード |
| 7 | `destination_kind_unsupported` | 未登録の `kind` |
| 8 | `destination_empty` | **`kind === mail` のみ**。`mail_to` に有効メールが0件 |
| 9 | `destination_saas_endpoint_empty` | **`kind === saas_backlog` のみ**。`saas_endpoint` trim 後空 |
| 10 | `destination_saas_project_id_empty` | **`kind === saas_backlog` または `saas_asana`**。`saas_project_id` trim 後空 |
| 11 | `destination_credentials_api_key_empty` | **`kind === saas_backlog` のみ**。[credentials 非空判定](#credentials-非空判定) で `api_key` 不備 |
| 12 | `destination_credentials_issue_type_id_empty` | **`kind === saas_backlog` のみ**。`issue_type_id` 不備 |
| 13 | `destination_credentials_priority_id_empty` | **`kind === saas_backlog` のみ**。`priority_id` 不備 |
| 14 | `destination_credentials_access_token_empty` | **`kind === saas_asana` のみ**。`access_token` 不備 |

`kind === saas_asana` では、`saas_endpoint` 空を **不足にしません** ([destination_spec.md](./destination_spec.md))。

同一送信に複数不足がある場合、**上表の順** で返す (安定順)。

## credentials 非空判定

* `destination.credentials` がない、または **array でない** → 当該 kind の credentials 系コードを、**キーごとにすべて** 不備とみなす。
* 各キーの値は次のいずれかなら **非空** とする:
  * `string` で trim 後が空でない
  * `int` で `> 0`
* 上記以外 (`null`, `float`, 空配列, `0`, 空白のみ string 等) → 不備。

材料組立時、`issue_type_id` / `priority_id` / `projectId` は **(string) キャスト** して HTTP に載せる (数値 ID の string 可)。

## Mail / SaaS 共通

* SaaS 時の `mail_to` は **検証しない** (空配列可)。Mail 時の `saas_*` / `credentials` も **検証しない** (未使用)。

## 例外

* プログラマー誤り (引数が配列でない、`build_dispatch_material` を deficiencies 非空で呼ぶ等) は `InvalidArgumentException`。
* 不足コードにはしない。

## 関連

* ペイロード: [payload_spec.md](./payload_spec.md)
* 公開 API: [../interfaces/php_api_spec.md](../interfaces/php_api_spec.md)
