# S2J Inquiry Destination Service - 実装モジュール対応

本ドキュメントは、[service_spec.md §実装順](./service_spec.md#実装順-kis-エコシステムとの関係) に沿った **実装タスクとファイル対応** です。コードは `src/` が正、本表は索引です。

## フェーズ1: 本 repo (WP なし)

| 順 | タスク | 仕様 | 想定 `src/` |
| --- | --- | --- | --- |
| 1 | Composer スケルトン、公開4関数の stub | [php_api_spec.md](./interfaces/php_api_spec.md) | `src/functions.php` |
| 2 | ペイロード正規化 | [core/payload_spec.md](./core/payload_spec.md) | `Core/Payload.php` |
| 3 | 検証 + 不足コード | [core/validation_spec.md](./core/validation_spec.md) | `Core/Validate.php` |
| 4 | dispatch + Mail 材料 | [dispatch_spec.md](./core/dispatch_spec.md), [mail_adapter_spec.md](./core/mail_adapter_spec.md) | `Core/Dispatch.php`, `Core/Adapters/Mail.php` |
| 5 | 結果写像 | [core/result_spec.md](./core/result_spec.md) | `Core/Result.php` |
| 6 | PHPUnit 必須分岐 | [testing_spec.md](./testing_spec.md) | `tests/Unit/*`, `tests/Fixtures/*` |
| 7 | PHPStan / PHPCS / CI | [engineering/ci.md](./engineering/ci.md) | `.github/workflows/*` |

## フェーズ2: プラグイン (別 repo)

S2J Inquiry Destination が `composer require` し、Snow Monkey Forms + `wp_mail` を接続 ([usage_spec.md](./interfaces/usage_spec.md))。

## フェーズ3: SaaS (未定)

製品確定後、PR では下記を **すべて** 満たします (空 stub 禁止)。

| # | タスク | 仕様 / ファイル |
| --- | --- | --- |
| 1 | `kind` を辞書で定義 (`saas_{product}`) | [data_dictionary.md](./contracts/data_dictionary.md) |
| 2 | dispatch レジストリに1行追加 | [core/dispatch_spec.md](./core/dispatch_spec.md) |
| 3 | アダプタ関数を実装 | `Core/Adapters/{Product}.php` |
| 4 | HttpRequestMaterial を契約に追記 | [contracts/data_contract_spec.md](./contracts/data_contract_spec.md) |
| 5 | 不足 / failure コードを必要なら minor 追加 | [validation_spec.md](./core/validation_spec.md), [data_dictionary.md](./contracts/data_dictionary.md) |
| 6 | PHPUnit 代表入力 + 写像 | [testing_spec.md](./testing_spec.md) |
| 7 | プラグイン: HTTP 実行、credentials 渡し、ログ (PII なし) | 別 repo [usage_spec.md](./interfaces/usage_spec.md) |

## 受け入れ (フェーズ1完了)

* [status.md](./status.md) の Composer / Core / Mail / 品質ゲートを100% にできるテスト本数
* 確定仕様 (`docs/`) が [specs.md](./specs.md) からたどれる

## 関連

* 統合見取り図: [service_spec.md](./service_spec.md)
