# S2J Inquiry Destination Service - 統一ペイロード

本ドキュメントは、プラグインが渡す **問い合わせ統一ペイロード** の意味と正規化を定義します。

## 責務

* フィールドの意味を定義すること。
* 正規化 (trim、型 coerce、未知キー) の規則を定義すること。

## 非責務

* SMF のフィールド名 → ペイロードキーへの写像 (プラグイン)。
* フォーム ID → `inquiry_type` の解決 (プラグイン)。
* 種別カタログ (増減・表示名・`mail_to`) — **プラグイン** ([../contracts/data_dictionary.md](../contracts/data_dictionary.md))。

## ペイロードの形

契約 JSON は [../contracts/data_contract_spec.md](../contracts/data_contract_spec.md) が正です。

| フィールド | 型 | 説明 |
| --- | --- | --- |
| `inquiry_type` | string | 種別 **slug**。プラグインが解決済みの値だけを渡す |
| `name` | string | 氏名。空は不足 |
| `email` | string | 連絡用メール。空または不正形式は不足 |
| `phone` | string | 電話。任意。空可 |
| `message` | string | 問い合わせ本文。空は不足 |
| `subject_hint` | string | 件名ラベル用 (種別表示)。任意。空可 |
| `locale` | string | BCP-47または `ja` / `en` 等。任意。空なら `""` |
| `meta` | object | プラグイン拡張。任意。空オブジェクト可 |

`inquiry_type` は **slug 形式** のみ Core が検証します (空または形式不正 → 不足 `inquiry_type_invalid`)。辞書に slug が登録されているかは Core の責務ではありません (例外にしない)。

### slug 形式 (Core)

* 正規表現 (例): `^[a-z][a-z0-9_]{0,63}$`
* 例: `sales`, `recruit`, `support`, `product_feedback`

### 初版 Mail アダプタで参照しないフィールド

* `locale` / `meta`: 正規化・検証では受け付けるが、初版 [mail_adapter_spec.md](./mail_adapter_spec.md) は **参照しない** (前方互換の予約)。
* 件名の表示文言は、**プラグイン** が `subject_hint` に載せるのが正。空時は Core が **slug をそのまま** 件名ラベルに使う。

## 正規化方針

* 文字列フィールドは、前後空白を trim する。
* 欠落キーは、下表どおり埋める。**入力にないキーを、出力に足さない** 方針のフィールドは [validation_spec.md](./validation_spec.md) と整合させる。
* `meta` 欠落 → `[]`。非配列 → `[]` に coerce してよい。
* 未知のトップレベルキーは **除去** する (前方互換のため受け取り、正規化後には載せない)。

## 関連

* 検証: [validation_spec.md](./validation_spec.md)
* 契約: [../contracts/data_contract_spec.md](../contracts/data_contract_spec.md)
