# S2J Inquiry Destination Service - 実装状況

最終更新: 2026-10-19

索引は [specs.md](./specs.md) です。統合見取り図は [service_spec.md](./service_spec.md) です。規則の正本は [core/](./core/payload_spec.md) と [contracts/](./contracts/data_contract_spec.md) です。

## 機能一覧 (起草)

| 機能名 | 実装％ (目安) | 完了条件 |
| --- | --- | --- |
| 仕様 (`docs/`) | — | [specs.md](./specs.md) 索引 |
| Composer + 公開 API | 0 | [php_api_spec.md](./interfaces/php_api_spec.md) |
| ペイロード正規化 | 0 | [payload_spec.md](./core/payload_spec.md) |
| 検証 | 0 | [validation_spec.md](./core/validation_spec.md) |
| Mail アダプタ | 0 | [mail_adapter_spec.md](./core/mail_adapter_spec.md) |
| 結果写像 | 0 | [result_spec.md](./core/result_spec.md) |
| PHPUnit / PHPStan / PHPCS | 0 | [testing_spec.md](./testing_spec.md), [ci.md](./engineering/ci.md) |
| Packagist | 0 | [build_and_release.md](./engineering/build_and_release.md) |
| プラグイン require | 0 | S2J Inquiry Destination 側 |

## 次のアクション

[implementation.md §フェーズ 1](./implementation.md#フェーズ-1--本-repo-wp-なし) に従い、`src/` と `tests/Unit/` を追加する。
