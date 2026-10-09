# S2J Inquiry Destination Service - CHANGELOG

## unreleased

## 0.0.1 - 2026-10-09

### Changed

* `docs_mod/service_spec.md` のログ項目を「HTTP ステータス・コード」に言い換えた

## 0.0.1 - 2026-10-08

### Changed

* `docs_mod/service_spec.md` の条件と列挙の言い方を、ドキュメント lint に合わせた
    * 条件は「場合」「際」、列挙は「下記」

## 0.0.1 - 2026-10-05

### Changed

* `docs_mod/service_spec.md` に、送信失敗後の再表示、自動返信、メール宛先、ログ、API キーの読み順を追記した
    * 送信失敗のあと、プラグインがそのリクエストの送信値を入力画面の欄に戻す。本ライブラリは画面を知らない
    * 管理者宛の成功後に自動返信だけが失敗しても、成功のまま完了画面に進む
    * メール宛先は、同じ1通の To として複数アドレスを持つことができる。CC と BCC は持たない
    * ログの出力先はプラグインの `error_log` だけにする。本ライブラリはログを書かない
    * SaaS のエンドポイントと API キーはサイトに1組。プラグインは環境変数か `wp-config.php` の定数を先に読む

## 0.0.1 - 2026-10-04

### Changed

* `docs_mod/service_spec.md` で、呼び出し側プラグインを [s2j-inquiry-destination](https://github.com/stein2nd/s2j-inquiry-destination) に確定し、仕様ドラフトへの参照を追加した
    * フォームは Snow Monkey Forms。対応付けたフォームの管理者宛メールは、このプラグインが送る
* `@s2j/docs-linter` を ^1.0.27に更新

## 0.0.1 - 2026-10-03

### Changed

* `@s2j/docs-linter` を ^1.0.26に更新
* `composer.lock` の `phpunit/phpunit` を v13.4.0に更新
* `.vscode/settings.json` の `npm.enableScriptExplorer` を `json.schemaDownload.enable` に変更

## 0.0.1 - 2026-10-01

### Changed

* `docs_mod/service_spec.md` の未決を決定に更新した
    * 氏名、連絡先、本文は送信するメールまたは SaaS チケットにだけ残し、結果レコードとログには残さない
    * 宛先はタイプごとに必ず指定する。空なら送らず、失敗にする
    * アダプタは本ライブラリに同梱し、種類ごとの関数とする。いま実装するのは Mail だけ
    * 再送キューは持たない。成功と分かるまでフォームを閉じない
    * 種別と Snow Monkey Forms のフォーム ID の対応は、プラグインの管理画面に保存する

## 0.0.1 - 2026-09-29

### Added

* 確定前の設計メモを `docs_mod/` に追加 (概要、コンセプト、設計原則、アーキテクチャー、サービス仕様、実装タスク、実装状況、テスト仕様、テスト結果)
* `docs_mod/specs.md` から `service_spec.md` への参照を追加

### Changed

* `.textlintrc.json` の allowlist に、サービス名と `PHPMailer`、`wp_mail` などを追加

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
