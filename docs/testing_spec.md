# S2J Inquiry Destination Service - テスト戦略

## 設計意図 (ゴール)

WordPress も `wp_mail` も SaaS API も使わずに、正規化、検証、Mail 材料、結果写像の全分岐を固定します。

## 責務

* 何を、どのレベルでテストするかを、正本とすること。

## 非責務

* CI YAML の細部 ([engineering/ci.md](./engineering/ci.md))
* プラグイン e2e / SMF
* 生カバレッジ HTML を仕様に貼ること

## テストレベル

| レベル | 内容 | 環境 |
| --- | --- | --- |
| Unit | 公開4関数 + Core | PHPUnit、WP なし |
| 契約スナップショット (任意) | 代表 Mail 本文・正規化後 DTO | PHPUnit + `tests/Fixtures/` ([data_contract_spec.md §PHP と JSON](../contracts/data_contract_spec.md)) |
| プラグイン結合 | SMF → wp_mail | プラグイン repo |

## 必須カバレッジ (初版)

* 各 **deficiency コード** について真になる入力を1件以上
* 複数不足の **安定順**
* `destination_inquiry_type_mismatch`
* `destination_empty` (`mail_to` 空、または不正形式のみで有効アドレス0件)
* `build_dispatch_material`: 正常 Mail 材料 (to / subject / body / content_type)
* `map_dispatch_result`: mail true / false、`context['correlation_id']` エコー
* 正規化: trim、未知キー除去、`meta` 非配列 coerce
* `InvalidArgumentException`: validate 失敗状態で build を呼ぶ

## 外部依存

* 時刻・乱数に依存しない。
* `wp_mail` はモックしない (プラグイン側)。本 repo では bool を `map_dispatch_result` に渡すだけ。

## 実行

```bash
composer test
```

## 生成物

| 種類 | 置き場 | Git |
| --- | --- | --- |
| カバレッジ | `/coverage/` | ignore |
| PHPUnit キャッシュ | `.phpunit.cache/` | ignore |

## 関連

* Core: [core/validation_spec.md](./core/validation_spec.md)
* CI: [engineering/ci.md](./engineering/ci.md)
