# S2J Inquiry Destination Service - Mail アダプタ

初版で実装する **唯一のアダプタ** です。`wp_mail` に渡す直前の材料を返します。

## 責務

* 件名・本文・ヘッダー (To) の組立規則を定義すること。
* ペイロードの PII を **本文に含める** (送信先向け)。結果レコードには載せない ([result_spec.md](./result_spec.md))。

## 非責務

* HTML メール / 添付 (初版は plain 想定。必要なら minor で拡張)。
* 自動返信メール。
* From / Reply-To の決定 (プラグインが `wp_mail` 呼び出し時に与える)。

## MailMaterial

| フィールド | 型 | 説明 |
| --- | --- | --- |
| to | list&lt;string&gt; | 正規化済みメールアドレス。`destination.mail_to` と同順 |
| subject | string | 件名。種別ラベルを含めてよい |
| body | string | プレーンテキスト。氏名、`email`、`phone`、`message` を含む |
| content_type | string | 初版 `text/plain` 固定 |

`content_type` は MIME タイプ (例: `text/plain`) のみ。`charset=UTF-8` は **プラグイン** が `wp_mail` ヘッダーで付与する ([usage_spec.md](../interfaces/usage_spec.md))。

## 件名

* 形式 (例): `[{inquiry_type}] {subject_hint または slug}` (`subject_hint` 空時は slug — [payload_spec.md](./payload_spec.md))。
* **初版**: 人が読む文言の i18n は **プラグイン** が `subject_hint` に載せるのが正。`subject_hint` 空時は Core が **`inquiry_type` slug をそのまま** 件名ラベルに使う (v0.2.0以降)。
* **将来 (minor)**: `locale` を参照した Core 側 i18n を足す場合は、本 spec と [payload_spec.md](./payload_spec.md) を同時更新する。

## 本文

必須行 (順序固定):

1. 種別 (`inquiry_type`)
2. 氏名 (`name`)
3. メール (`email`)
4. 電話 (`phone` — 空なら行ごと省略可)
5. 本文 (`message`)

区切りは `\n\n` 推奨。実装はスナップショットテストで固定。

## 成功条件 (プラグイン ↔ 結果写像)

Mail の成功は **受信箱到達の確認不可** とします。`wp_mail` が `true` を返した時点を成功とします ([service_spec.md](../service_spec.md))。

## 関連

* 契約: [../contracts/data_contract_spec.md](../contracts/data_contract_spec.md)
* dispatch: [dispatch_spec.md](./dispatch_spec.md)
