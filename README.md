# S2J Inquiry Destination Service

問い合わせ送信を統一ペイロードに正規化し、Mail / 将来 SaaS 向けの送信材料を組み立てる WordPress 非依存の Composer ライブラリです。

## 仕様

確定仕様は [docs/specs.md](./docs/specs.md) からたどります。改訂起草は [docs_mod/README.md](./docs_mod/README.md) です。

## インストール

PHP **8.1以上** が必要です。

```bash
composer require s2j/inquiry-destination-service:^0.1.0
```

Packagist 未公開の間は VCS またはパス repository を利用します。

## 開発

```bash
composer install
composer test
composer lint:php
npm run quality:php   # validate + dist + PHPUnit + PHPStan + PHPCS
```
