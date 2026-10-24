# S2J Inquiry Destination Service — 仕様の起草場

**確定仕様の正本** は [`../docs/`](../docs/) です。索引は [../docs/specs.md](../docs/specs.md) からたどります。

## ミラー規則

| 確定 (`docs/`) | 起草 (`docs_mod/`) |
| --- | --- |
| `docs/core/dispatch_spec.md` | `docs_mod/core/dispatch_spec.md` |
| … | **同じ相対パス** |

確定反映時は [documentation_governance.md §マージ](../docs/governance/documentation_governance.md) に従い、**同名パスで `docs/` を更新** します (`_v03` 等の別名ファイルは作らない)。

## 直近イニシアチブ

| Slug | 状態 |
| --- | --- |
| **v0.3-saas** | **2026-10-24** に `docs/` に反映済 |

次の改訂を始めるときは、本ディレクトリに **ミラー spec** と [ガバナンスの三点セット](../docs/governance/documentation_governance.md) (`modification.md` / `status.md` / `test-results.md`) を置いて起草します。反映後、ミラー `.md` は削除して **本 README のみ** 残してよいです。

## 関連

* [実装状況](../docs/status.md)
