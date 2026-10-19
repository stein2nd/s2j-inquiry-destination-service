# S2J Inquiry Destination Service - 検証

本ドキュメントは、送信前検証と **不足コード (deficiencies)** を定義します。

## 責務

* 不足コードの enumeration と安定順序を定義すること。
* 宛先の未設定時に **送らない** ことを定義すること。

## 非責務

* プラグイン UI のエラーメッセージ (i18n はプラグイン)。

## 検証の流れ

1. `normalize_inquiry_payload` を適用 (公開 API 内で呼んでもよい)。
2. ペイロード項目を検証。
3. 宛先レコードを **正規化せず** 検証のみ (`inquiry_type` 一致、`kind` 登録済み、Mail なら `mail_to`)。未知キーの除去や型 coerce は行わない。形の正しさはプラグインが [data_contract_spec.md](../contracts/data_contract_spec.md) に合わせて渡す。
4. 戻り: `{ payload, destination, deficiencies: list<string> }`。`payload` は正規化済み。`destination` は入力をそのまま返す (deficiencies 空の場合)。

**deficiencies が1件でもあれば、材料組立は行いません** (Webinar Service の build 前 validate と同型)。

## 不足コード (初版)

| コード | 条件 |
| --- | --- |
| `inquiry_type_invalid` | 未知の `inquiry_type` |
| `name_empty` | `name` が空 |
| `email_empty` | `email` が空 |
| `email_invalid` | `email` が簡易 RFC 非準拠 (実装は filter_var 等) |
| `message_empty` | `message` が空 |
| `destination_inquiry_type_mismatch` | 宛先の `inquiry_type` ≠ ペイロード |
| `destination_kind_unsupported` | 未登録の `kind` |
| `destination_empty` | Mail で `mail_to` を走査し、`filter_var(..., FILTER_VALIDATE_EMAIL)` 等で **有効なアドレスが0件** (空配列、空白のみ、不正形式を含む) |

同一送信に複数不足がある場合、**上表の順** で返す (安定順)。

## 例外

* プログラマー誤り (引数が配列でない、`build_dispatch_material` を deficiencies 非空で呼ぶ等) は `InvalidArgumentException`。
* 不足コードにはしない。

## 関連

* ペイロード: [payload_spec.md](./payload_spec.md)
* 公開 API: [../interfaces/php_api_spec.md](../interfaces/php_api_spec.md)
