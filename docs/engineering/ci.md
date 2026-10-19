# S2J Inquiry Destination Service - CI

Webinar Survey Service / Webinar Service と同型の **php-quality** ゲートを想定します。

## 必須 (merge 前)

| ステップ | コマンド |
| --- | --- |
| PHPUnit | `composer test` |
| PHPStan | `composer phpstan` |
| PHPCS | `composer phpcs` |
| docs-lint | `npm run lint:docs` (導入時) |

## ワークフロー (起草)

* `.github/workflows/php-quality.yml` — push / PR で `composer install` → test / phpstan / phpcs
* `.github/workflows/docs-lint.yml` — `docs/**` と `docs_mod/**` (起草 README 含む)

## 非目標

* WordPress 統合テスト (プラグイン repo)
* ライブ `wp_mail` / SaaS API

## 関連

* テスト規則: [../testing_spec.md](../testing_spec.md)
* リリース: [build_and_release.md](./build_and_release.md)
