# S2J Inquiry Destination Service - CHANGELOG

## unreleased

## 0.0.1 - 2026-09-28

### Added

* `composer.json` を追加 (v0.0.1、`s2j/inquiry-destination-service`)。PHP `>=8.0`、オートロードは `S2J\InquiryDestinationService\` → `src/`
* 開発用依存に `phpunit/phpunit` ^13.1、`phpstan/phpstan` ^2.1、`squizlabs/php_codesniffer` ^4.0を追加

* `package.json` を追加 (v0.0.1)。説明は問い合わせ SaaS アダプタ (WordPress 非依存)
* ドキュメント lint を追加 (`@s2j/docs-linter` ^1.0.25、`npm run lint:docs`)
* npm v12以降向けに `.npmrc` の `allow-git=all` と `package.json` の `allowScripts` を追加

### Changed

* README の見出しを `S2J Inquiry Destination Service` に変更
* `.gitignore` を Composer、Node、テスト成果物向けに拡張
