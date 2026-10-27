# v0.4-transport — 改訂メモ (起草)

**状態:** 起草中。**イニシアチブ索引:** [release_v0.4_spec.md](./release_v0.4_spec.md) (常設仕様の正本は確定後の `docs/` — [README §正本の階層](./README.md#正本の階層))。

## 目的 (1行)

プラグイン v0.2の「Mail 認証は Transport 層」方針に合わせ、Service **0.4.0** で Transport 境界を docs に固定します (公開 API は v0.3 互換)。

## スコープ

| 含む | 含まない |
| --- | --- |
| Transport / Email Auth の Service 非目標の明文化 | `build_dispatch_material` への DKIM / SMTP パラメータ |
| `MailMaterial` 4キー固定の契約強化 | 新 SaaS `kind` |
| `usage_spec` の Runner ヘッダー一覧 | Mail HTML / multipart 材料 |
| Packagist **0.4.0**、CHANGELOG、CI green | `inquiry_type` 規則変更 |

## ミラー spec (確定時 `docs/` に)

| 起草 (`docs_mod/`) | 確定操作 |
| --- | --- |
| [core/transport_boundary_spec.md](./core/transport_boundary_spec.md) | **新規** `docs/core/` 同名 |
| [overview.md](./overview.md) | 非目的・非責務に Transport を追記 |
| [principles.md](./principles.md) | Transport SoT 行を追記 |
| [architecture.md](./architecture.md) | Runner / Transport 注記 |
| [service_spec.md](./service_spec.md) | §Transport 境界 + 改訂履歴 |
| [core/mail_adapter_spec.md](./core/mail_adapter_spec.md) | 除外キー § |
| [interfaces/usage_spec.md](./interfaces/usage_spec.md) | Mail Runner §、Composer `^0.4.0` |
| [specs.md](./specs.md) | 索引 + SoT 行 |
| [contracts/data_dictionary.md](./contracts/data_dictionary.md) | 用語 `Transport 層` |
| [contracts/data_contract_spec.md](./contracts/data_contract_spec.md) | Mail 出力注記 |

実装コード変更は **原則不要** (doc + tag)。`src/` 変更が出た場合は [implementation.md](../docs/implementation.md) と [status.md](./status.md) を同期する。

## Companion プラグイン

| Repo | 版 | 作業 |
| --- | --- | --- |
| [s2j-inquiry-destination](https://github.com/stein2nd/s2j-inquiry-destination) | v0.2.0 | `^0.4.0` require、[mail_deliverability_spec.md](https://github.com/stein2nd/s2j-inquiry-destination/blob/main/docs_mod/mail_deliverability_spec.md) から本 `transport_boundary_spec` にリンク |

## マージ前チェック

* [ ] ミラー間で `MailMaterial` キー表記が4つで一致
* [ ] `Transport 層` / `Email Auth` / `プラグイン Runner` の用語が全ミラーで同義
* [ ] `npm run lint:docs` green
* [ ] PHPUnit 39 / スナップショット不変 ([test-results.md](./test-results.md))
* [ ] [release_v0.4_spec.md §完了条件](./release_v0.4_spec.md#5-完了条件) を満たす

## 関連

* [README.md](./README.md) — ミラー規則
* [status.md](./status.md) — 進捗
* [test-results.md](./test-results.md) — 検証記録
