# S2J Inquiry Destination Service - ドキュメンテーション・ガバナンス

## Source of Truth

矛盾時は **core / contracts** を正とし、`service_spec.md` / `README` を追随させます。

| 対象 | 正本 |
| --- | --- |
| 規則 | `docs/core/*` |
| 型 | `docs/contracts/*` |
| 公開 PHP API | `docs/interfaces/php_api_spec.md` |
| 統合見取り図 | [service_spec.md](../service_spec.md) (要約) |
| 最短手順 | ルート `README.md` |

## 改訂フロー

* 確定仕様の正本は `docs/` です。
* 改訂案の起草は [docs_mod/](../../docs_mod/README.md) に置き、合意後に `docs/` に反映します。
* [engineering/build_and_release.md §PHP 要件](../engineering/build_and_release.md) を変更したら、ルート `composer.json` の `require.php` を **同じ PR** で更新する。
* フィールド規則を `service_spec.md` に書く場合は、**core / contracts へのリンクを優先** し、二重定義を増やさない。

### 起草レイアウト (ミラー)

`docs/` と **同じ相対パス** で `docs_mod/` に置きます (例: `docs/core/dispatch_spec.md` → `docs_mod/core/dispatch_spec.md`)。確定時に **別名ファイル ( `_v03` 等) は作らない**。

* v0.3SaaS (Backlog / Asana) イニシアチブも、上記ミラーを使う。
* 新規 adapter 正本は `docs_mod/core/saas_*_adapter_spec.md` として起草し、確定後 `docs/core/` に **同名で新規追加** する。
* イニシアチブの進捗・スコープは `docs_mod/modification.md` / `status.md` / `test-results.md` (三点セット) に置く。長い規則本文はミラー spec に書く。

### `docs_mod/` から `docs/` への反映 (マージ)

[Webinar Survey Service documentation_governance §マージ](https://github.com/stein2nd/s2j-webinar-survey-service/blob/main/docs/governance/documentation_governance.md) に倣います。

| 状況 | 操作 |
| --- | --- |
| 同じ見出し (`##` / `###` 等) がある | その見出しブロックの末尾に行を追加する。目印は `yyyy-mm-dd追記` とする (textlint 向けに日付と「追記」の間にスペースを入れない)。 |
| 同じ見出しがない | ファイル末尾に追記する (必要なら `## 改訂履歴` を新設してから追記してよい)。 |
| 新規ファイル (`saas_*_adapter_spec.md` 等) | `docs/` に同パスで追加する (上書き対象なし)。 |
| 合意した大規模リライト | 該当ファイルを **全面置換** してよい (v0.3SaaS の adapter 新規正本は通常こちらではない)。 |

反映後、起草側の仕様 `.md` (ミラー) は削除してよい。`docs_mod/README.md` と三点セット用ファイルの置き場は残す。イニシアチブ完了時は [archive/README.md](../archive/README.md) に freeze する。

## Lint

* `@s2j/docs-linter` / `npm run lint:docs`
* 対象: `README.md`, `CHANGELOG.md`, `docs/**/*.md`, `docs_mod/**/*.md`

## 関連

* 索引: [../specs.md](../specs.md)
