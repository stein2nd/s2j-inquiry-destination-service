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
* 改訂案の起草は [docs_mod/README.md](../../docs_mod/README.md) に置き、合意後に `docs/` に反映します。
* マージ手順は Webinar Survey Service の [documentation_governance](https://github.com/stein2nd/s2j-webinar-survey-service/blob/main/docs/governance/documentation_governance.md) §マージ に倣います。`yyyy-mm-dd追記` 目印可。
* [engineering/build_and_release.md §PHP 要件](../engineering/build_and_release.md) を変更したら、ルート `composer.json` の `require.php` を **同じ PR** で更新する。
* フィールド規則を `service_spec.md` に書く場合は、**core / contracts へのリンクを優先** し、二重定義を増やさない。

## Lint

* `@s2j/docs-linter` / `npm run lint:docs`
* 対象: `README.md`, `CHANGELOG.md`, `docs/**/*.md`, `docs_mod/**/*.md`

## 関連

* 索引: [../specs.md](../specs.md)
