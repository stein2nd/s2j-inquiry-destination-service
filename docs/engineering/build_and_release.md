# S2J Inquiry Destination Service - ビルドおよびリリース

## Packagist

| 項目 | 値 |
| --- | --- |
| パッケージ名 | `s2j/inquiry-destination-service` |

### PHP 要件 (本ドキュメントが正本)

[WordPress 必須要件](https://ja.wordpress.org/about/requirements/) に合わせ、本ライブラリの下限も **PHP v8.3** とします。

| 区分 | 要件 | 備考 |
| --- | --- | --- |
| ライブラリ declare 下限 | `^8.3` | ルート `composer.json` の `require.php` と **同時** に更新する |
| 本 repo 開発 / CI | PHP **8.3** ([php-quality.yml](../../.github/workflows/php-quality.yml)) | dev 依存は `composer.lock` に従う (PHPUnit v10系) |

他ドキュメント (`architecture.md`、`php_api_spec.md` 等) は本表を参照し、独自の PHP 版数を書かない。

## リリース手順 (Webinar Service 同型)

1. `CHANGELOG.md` に SemVer 節を追記
2. `composer test` / `composer lint:php` / docs-lint
3. Packagist auto-update 確認

## dist

* `composer archive` または `.gitattributes` + `verify-dist` スクリプト (Webinar Service の `composer verify-dist` を参考)
* `vendor/` はアーカイブに含めない (利用側で `composer install`)

## 関連

* CI: [ci.md](./ci.md)
* 公開 API SemVer: [../interfaces/php_api_spec.md](../interfaces/php_api_spec.md)
