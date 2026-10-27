# S2J Inquiry Destination Service — 仕様の起草場

**確定仕様の正本** は [`../docs/`](../docs/) です。索引は [../docs/specs.md](../docs/specs.md) からたどります。

## 正本の階層

1. **規則** — `docs/core/*` · `docs/contracts/*` · `docs/interfaces/*`
2. **統合見取り図** — [`docs/service_spec.md`](../docs/service_spec.md) (フィールド規則の重複定義は避け、`core/` にリンク)
3. **改訂案** — 同じ相対パスで `docs_mod/` にミラー (確定後 `docs/` にマージ。別名 `_v04` 等は作らない)

[`release_v0.*_spec.md`](./) 等の **リリース実装仕様** はイニシアチブ用の orchestration で、確定後は [../docs/archive/](../docs/archive/README.md) に freeze し、規則本文は `docs/` に吸収する。

## ミラー規則

確定反映時は [documentation_governance.md §マージ](../docs/governance/documentation_governance.md) に従い、**同名パスで `docs/` を更新** します。

| 確定 (`docs/`) | 起草 (`docs_mod/`) |
| --- | --- |
| `docs/core/dispatch_spec.md` | `docs_mod/core/dispatch_spec.md` |
| … | **同じ相対パス** |

## 直近イニシアチブ

| Slug | 状態 |
| --- | --- |
| **v0.3-saas** | **2026-10-24** に `docs/` に反映済 |
| **v0.4-transport** | **2026-10-27** に `docs/` に反映済 — 証跡 [../docs/archive/mod-v0.4-transport/](../docs/archive/mod-v0.4-transport/) |

次の改訂を始める場合は、本ディレクトリに **ミラー spec** と三点セット (`modification.md` / `status.md` / `test-results.md`) を置いて起草する。反映後、ミラー `.md` は削除して **本 README のみ** 残してよい。

## 関連

* [実装状況](../docs/status.md)
