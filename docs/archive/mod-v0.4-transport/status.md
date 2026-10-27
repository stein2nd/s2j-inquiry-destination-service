# v0.4-transport — 起草進捗

**最終更新:** 2026-10-27

| 区分 | 状態 | 備考 |
| --- | --- | --- |
| リリース仕様 | 起草 | [release_v0.4_spec.md](./release_v0.4_spec.md) |
| ミラー spec | 起草完了 | [modification.md §ミラー](./modification.md#ミラー-spec-確定時-docs-に) |
| `docs/` 反映 | 未 | 合意後にマージ (別名ファイルなし) |
| `src/` 変更 | 不要 (想定) | API / MailMaterial 形状は v0.3 維持 |
| PHPUnit | 未記録 | [test-results.md](./test-results.md) |
| Packagist 0.4.0 | 未 | tag / CHANGELOG は実装フェーズ |

## ミラー一覧 (レビュー順)

1. [core/transport_boundary_spec.md](./core/transport_boundary_spec.md) — **規則 SoT**
2. [core/mail_adapter_spec.md](./core/mail_adapter_spec.md)
3. [interfaces/usage_spec.md](./interfaces/usage_spec.md) §Mail Runner
4. [overview.md](./overview.md) / [principles.md](./principles.md) / [architecture.md](./architecture.md)
5. [service_spec.md](./service_spec.md) §Transport 境界
6. [specs.md](./specs.md) / [contracts/data_dictionary.md](./contracts/data_dictionary.md) / [contracts/data_contract_spec.md](./contracts/data_contract_spec.md)

## 用語チェック (ミラー間)

| 用語 | 定義の正本 |
| --- | --- |
| MailMaterial 4キー | `transport_boundary_spec` + `mail_adapter_spec` |
| Transport 層 / Email Auth | `transport_boundary_spec` |
| プラグイン Runner | `transport_boundary_spec` + `usage_spec` §Mail Runner |
