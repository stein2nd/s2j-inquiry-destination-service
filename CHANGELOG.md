# S2J Inquiry Destination Service - CHANGELOG

## unreleased

## 0.4.1 - 2026-10-29

### Changed

* **`docs/status.md`**: Packagist [v0.4.0](https://packagist.org/packages/s2j/inquiry-destination-service#v0.4.0) 反映を確認し、非 HTTPS、Packagist 完了条件を **7/7** に更新
* **dev**: `composer.lock` — `phpstan/phpstan` 2.3.4、`@s2j/docs-linter` ^1.0.31

## 0.4.0 - 2026-10-27

### Changed

* **Transport 境界 (Mail)**: Service は `MailMaterial` 4キーのみ。SMTP / DKIM / From はプラグイン Runner — [transport_boundary_spec.md](./docs/core/transport_boundary_spec.md)、[usage_spec.md § Mail Runner](./docs/interfaces/usage_spec.md)
* **`docs/`**: `overview` / `principles` / `service_spec` / `mail_adapter_spec` / contracts 索引を v0.4に同期
* **README**: `composer require ^0.4.0`、Transport 非目標へのリンク
* **`docs/status.md`**: v0.3.0リリース、Packagist `0.3.0`、非 HTTPS 完了条件 (Impl / CI 証跡・チェック件数) を反映

### Added

* **PHPUnit**: Mail 材料キーが4つのみであることを `testBuildDispatchMaterialProducesMailMaterial` で固定 (v0.4契約)

### Fixed

* **`php-quality` CI**: `require.php` の caret (`^8.3`) 下限を `8.3.0` として assert (`version_compare`)。`^8.3` を `8.3` と解釈して落ちていた Step を修正

## 0.3.0 - 2026-10-24

### Added

* **SaaS `kind`**: `saas_backlog` (Backlog 課題 form) / `saas_asana` (Asana JSON `data` ラッパー) — `Core/Adapters/SaasBacklog.php`, `SaasAsana.php`
* **検証**: 不足コード6件 (`destination_saas_*`, `destination_credentials_*`)。`destination_kind_unsupported` 時は kind 専用行を評価しない
* **写像**: `map_dispatch_result` — `SaasExecutionOutcome`、成功は HTTP **201** + ticket id、`saas_http_error` / `saas_body_invalid`
* **`Core/Adapters/InquiryContent`**: Mail / SaaS 共通の件名・プレーン本文 (`Mail` を共有化)
* **PHPUnit**: `SaasInquiryDestinationServiceTest` — [testing_spec.md](./docs/testing_spec.md) v0.3分岐 (計 **39** tests)

### Changed

* **PHP 要件**: `^8.3` ([WordPress 必須要件](https://ja.wordpress.org/about/requirements/) 準拠)。`build_and_release.md`、README、`.github/workflows/php-quality.yml` を同期
* **`docs/`**: v0.3 (SaaS) 仕様確定 (Backlog / Asana adapter 新規、dispatch / validation / contracts / usage 更新)
* **`docs/status.md`**: 非 HTTPS 完了条件に SaaS 検証・材料・写像を反映
* **`build_dispatch_material`**: `material` 型を `array<string, mixed>` (Mail / HttpRequestMaterial 判別)
* **`docs_mod/`**: 反映後は [README.md](./docs_mod/README.md) のみ残す (次回起草用)

## 0.2.0 - 2026-10-20

### Breaking Changes

* **`inquiry_type`**: Core の固定列挙 (`sales` / `recruit` のみ) を廃止。プラグインが渡す **slug** は形式検証のみ (`inquiry_type_invalid` = 空または `^[a-z][a-z0-9_]{0,63}$` 非準拠)。v0.1では `event` 等は拒否されていたが、v0.2以降は有効 slug なら受理する。
* **Mail 件名 (`subject_hint` 空時)**: 英語固定ラベル (`Sales inquiry` 等) を廃止。**slug をそのまま** 件名ラベルに使う。表示名はプラグインが `subject_hint` で渡す前提。

### Changed

* `docs/`: [payload_spec.md](./docs/core/payload_spec.md)、[validation_spec.md](./docs/core/validation_spec.md)、[data_dictionary.md](./docs/contracts/data_dictionary.md)、[mail_adapter_spec.md](./docs/core/mail_adapter_spec.md) — 種別カタログはプラグイン SoT
* `Core/InquiryTypes`: slug 形式検証に変更

### Added

* Test: 任意 slug (`product_feedback`) の正常パス、`inquiry_type_invalid` (空・形式不正)

## 0.1.0 - 2026-10-20

### Added

* 初版 Core と公開4関数 (`normalize_inquiry_payload`、`validate_inquiry_dispatch`、`build_dispatch_material`、`map_dispatch_result`)
* Mail アダプタ (件名、本文、`text/plain` 材料) と結果写像 (`mail_rejected`、`correlation_id` エコー)
* PHPUnit 必須分岐 + 契約スナップショット (`tests/Unit/`、`tests/Fixtures/contracts/mail_sales_body.txt`)
* GitHub Actions `php-quality` と `scripts/verify/all.sh` (validate / dist / PHPUnit / PHPStan / PHPCS)
* `composer validate-composer` / `composer verify-dist` (`scripts/verify-composer-dist.sh`)
* `docs/status.md` の機能一覧と非 HTTPS 完了条件チェックリスト

### Changed

* `composer.json` に `autoload.files`、dev 向け PSR-4、`archive` から `docs_mod` を除外
* README にインストール・開発コマンド (`npm run quality:php`) を追記
* `.gitattributes` に `docs_mod` を `export-ignore` した

### Fixed

* `php-quality` CI: `phpunit/phpunit` を ^10.5に合わせ、`composer.lock` を PHP v8.3 (Actions) と整合させた

## 0.0.1 - 2026-10-19

### Added

* 確定仕様を `docs/` に置いた (索引 `docs/specs.md`、`core/`、`contracts/`、`interfaces/`、`engineering/`、`governance/`)
* `docs/archive/README.md` を追加 (完了イニシアチブの凍結スナップショット用)

### Changed

* `docs_mod/` の仕様 Markdown を `docs/` に移行し、`docs_mod/README.md` のみ残して改訂起草場とした
* 仕様を横断レビューし、正規フロー、`kind` 命名、PHP SoT、契約 canonical 形などを `docs/` 内で統一した
* `composer.json` の PHP 要件を `^8.1` に更新
* README から `docs/specs.md` と `docs_mod/README.md` にリンクした

## 0.0.1 - 2026-10-15

### Changed

* `@s2j/docs-linter` を ^1.0.29に更新
* `composer.lock` の `phpstan/phpstan` を v2.3.1、`phpunit/phpunit` を v13.4.2に更新
* `.textlintrc.json` の allowlist に `#status-` 付きアンカー ID を追加した

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
