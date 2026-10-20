# S2J Inquiry Destination Service - 実装状況

本ページは、現状の実装状況を機能単位で一覧します (製品全体の「いま」)。
索引は [specs.md](./specs.md) です。統合見取り図は [service_spec.md](./service_spec.md) です。規則・型の Source of Truth は [core/](./core/payload_spec.md) と [contracts/](./contracts/data_contract_spec.md) です ([governance/documentation_governance.md](./governance/documentation_governance.md) と同じ)。

最終更新: 2026-10-20

## 仕様書 (参照元)

* [specs.md](./specs.md) — 索引
* [service_spec.md](./service_spec.md) — 統合見取り図 (要約。規則の正本ではない)
* [core/](./core/payload_spec.md) — 規則 (SoT)
* [contracts/data_contract_spec.md](./contracts/data_contract_spec.md) — DTO (SoT)
* [interfaces/php_api_spec.md](./interfaces/php_api_spec.md) — 公開面

## 機能一覧

**完了条件列:** 100% 到達用チェックリスト (1行 = PHPUnit ケース1件、または CI / 手動ステップ1件)。`[x]` = 満たし済み、`[ ]` = 未。正本 spec は各§の見出しリンク。

| 機能名 | 実装済み/未実装 | 実装％ | 完了条件 (100%) |
| --- | --- | --- | --- |
| 仕様分割 (`docs/`) | 確定 | — | [§ 仕様分割](#status-spec-docs) |
| Composer スケルトン (`src/` 公開 API) | 実装済み | 100 | [§ Composer](#status-composer) (12/12) |
| ペイロード正規化 | 実装済み | 100 | [§ 正規化](#status-payload) (4/4) |
| 検証 (不足コード一式) | 実装済み | 100 | [§ 検証](#status-validate) (10/10) |
| Mail アダプタ | 実装済み | 100 | [§ Mail](#status-mail) (5/5) |
| 結果写像 | 実装済み | 100 | [§ 結果写像](#status-result) (4/4) |
| PHPUnit / PHPStan / PHPCS | 実装済み | 100 | [§ 品質ゲート](#status-quality) (8/8) |
| Packagist 公開 | 実装済み | 100 | [§ Packagist](#status-packagist) (5/5) |
| プラグインからの require | 未 | 0 | [§ プラグイン (HTTPS)](#status-plugin-https) (0/9) |

## 100% 完了条件チェックリスト

### 非 HTTPS 環境でテスト可能なこと

ローカルまたは CI 上で、WordPress、実 SMTP、ステージング URL なしに完結する項です。正本: [testing_spec.md](./testing_spec.md) (Unit)、[ci.md](./engineering/ci.md)。

一括実行: `npm run quality:php` (= `scripts/verify/all.sh` と同順)。

#### 仕様分割 {#status-spec-docs}

* [x] CI: `docs-lint` が `docs/**` 等を lint ([.github/workflows/docs-lint.yml](../.github/workflows/docs-lint.yml))
* [x] 正本が `docs/`、起草が `docs_mod/` ([governance/documentation_governance.md](./governance/documentation_governance.md))

#### Composer スケルトン {#status-composer}

正本: [php_api_spec.md](./interfaces/php_api_spec.md)

* [x] `composer.json` — `autoload.files` に `src/functions.php`
* [x] Test: `InquiryDestinationServiceTest::testNormalizeTrimsStringsAndCoercesMeta` — 公開 `normalize_inquiry_payload`
* [x] Test: `InquiryDestinationServiceTest::testValidateReturnsStableDeficiencyOrder` — 公開 `validate_inquiry_dispatch`
* [x] Test: `InquiryDestinationServiceTest::testSingleDeficiencyCodes` — 初版 deficiency 全8コード (data provider)
* [x] Test: `InquiryDestinationServiceTest::testDestinationEmptyWhenMailToHasOnlyInvalidAddresses` — `destination_empty` (不正のみ)
* [x] Test: `InquiryDestinationServiceTest::testBuildDispatchMaterialProducesMailMaterial` — 公開 `build_dispatch_material`
* [x] Test: `InquiryDestinationServiceTest::testBuildDispatchMaterialThrowsWhenNotValidated` — deficiencies 非空で `InvalidArgumentException`
* [x] Test: `InquiryDestinationServiceTest::testMapDispatchResultForMail` — 公開 `map_dispatch_result` (true / false / `correlation_id`)
* [x] CI: [.github/workflows/php-quality.yml](../.github/workflows/php-quality.yml) — `composer install` → PHPUnit
* [x] CI: `php-quality` — PHPStan level8
* [x] CI: `php-quality` — PHPCS 警告0 (`composer phpcs`)
* [x] CI: `composer validate-composer` + `composer verify-dist` ([build_and_release.md](./engineering/build_and_release.md))

#### ペイロード正規化 {#status-payload}

正本: [payload_spec.md](./core/payload_spec.md)

* [x] Test: `testNormalizeTrimsStringsAndCoercesMeta` — 文字列 trim
* [x] Test: `testNormalizeTrimsStringsAndCoercesMeta` — `meta` 非配列 → `[]`
* [x] Test: `testNormalizeTrimsStringsAndCoercesMeta` — 未知トップレベルキー除去
* [x] Test: `testValidateReturnsDestinationUnchanged` — `destination` を正規化せず入力どおり返す ([validation_spec.md](./core/validation_spec.md))

#### 検証 (不足コード) {#status-validate}

正本: [validation_spec.md](./core/validation_spec.md)

* [x] Test: `testSingleDeficiencyCodes` — `inquiry_type_invalid`
* [x] Test: `testSingleDeficiencyCodes` — `name_empty`
* [x] Test: `testSingleDeficiencyCodes` — `email_empty`
* [x] Test: `testSingleDeficiencyCodes` — `email_invalid`
* [x] Test: `testSingleDeficiencyCodes` — `message_empty`
* [x] Test: `testSingleDeficiencyCodes` — `destination_inquiry_type_mismatch`
* [x] Test: `testSingleDeficiencyCodes` — `destination_kind_unsupported`
* [x] Test: `testSingleDeficiencyCodes` — `destination_empty` (`mail_to` 空)
* [x] Test: `testValidateReturnsStableDeficiencyOrder` — 複数不足の安定順
* [x] Test: `testRecruitHappyPathValidateAndBuild` — `recruit` 正常パス (validate + build)

#### Mail アダプタ {#status-mail}

正本: [mail_adapter_spec.md](./core/mail_adapter_spec.md)

* [x] Test: `testBuildDispatchMaterialProducesMailMaterial` — `to` / `subject` / `body` / `content_type`
* [x] Test: `testBuildDispatchMaterialProducesMailMaterial` — `phone` 空時は本文に電話行を出さない
* [x] Test: `testBuildDispatchMaterialProducesMailMaterial` — 件名 `[{inquiry_type}] {subject_hint}`
* [x] Test: `testBuildUsesSubjectFallbackWhenSubjectHintEmpty` / `testRecruitHappyPathValidateAndBuild` — `subject_hint` 空時 ASCII フォールバック
* [x] Test: `ContractSnapshotTest::testMailSalesBodyMatchesContractFixture` — [tests/Fixtures/contracts/mail_sales_body.txt](../tests/Fixtures/contracts/mail_sales_body.txt)

#### 結果写像 {#status-result}

正本: [result_spec.md](./core/result_spec.md)

* [x] Test: `testMapDispatchResultForMail` — `execution_outcome === true` → `success: true`, `failure_code: ""`
* [x] Test: `testMapDispatchResultForMail` — `false` → `mail_rejected`
* [x] Test: `testMapDispatchResultForMail` — `context['correlation_id']` エコー
* [x] Test: `testMapDispatchResultForMail` — Mail 成功時 `external_ticket_id` 空文字、`http_status` `null`

#### PHPUnit / PHPStan / PHPCS {#status-quality}

正本: [testing_spec.md](./testing_spec.md) / [ci.md](./engineering/ci.md)

* [x] Test: [testing_spec.md](./testing_spec.md) 必須カバレッジ — Unit (`InquiryDestinationServiceTest` + `ContractSnapshotTest`、18 tests)
* [x] Local: `composer phpstan` がエラーなく通る (level8)
* [x] Local: PHPUnit 全緑 (`composer test`)
* [x] Local: PHPCS 警告0 (`composer lint:php` / `composer phpcs`)
* [x] Local: `npm run lint:docs`
* [x] CI: `docs-lint` ([.github/workflows/docs-lint.yml](../.github/workflows/docs-lint.yml))
* [x] CI: `php-quality` ([.github/workflows/php-quality.yml](../.github/workflows/php-quality.yml))
* [x] Local: `composer validate-composer` + `composer verify-dist` (`npm run quality:php`)

#### Packagist 公開 {#status-packagist}

正本: [build_and_release.md](./engineering/build_and_release.md)

* [x] Packagist: [s2j/inquiry-destination-service](https://packagist.org/packages/s2j/inquiry-destination-service) 登録済み (`dev-main`)
* [x] Packagist: auto-update — `This package is auto-updated.` を確認 (2026-10-20)
* [x] SemVer: CHANGELOG `0.1.0` 節 + タグ `v0.1.0` ([Packagist v0.1.0](https://packagist.org/packages/s2j/inquiry-destination-service#v0.1.0))
* [x] `composer validate-composer` / `composer verify-dist` (.gitattributes / `scripts/verify-composer-dist.sh`)
* [x] README — `composer require` / PHP v8.1以上 ([README.md](../README.md))

---

### HTTPS 環境でのみテスト可能なこと

ステージング / 本番 WordPress (HTTPS)、実メール到達、将来の SaaS 実 API 向けの項です。本 repo の PHPUnit では代替しません。プラグイン正本: [S2J Inquiry Destination](https://github.com/stein2nd/s2j-inquiry-destination) ([usage_spec.md §チェックリスト](./interfaces/usage_spec.md#プラグイン実装チェックリスト-mail-初版))。

#### プラグイン結合 (Mail 初版) {#status-plugin-https}

* [ ] プラグイン: `composer.json` — `s2j/inquiry-destination-service` を require
* [ ] プラグイン Unit: SMF 送信 → 統一ペイロード翻訳 (WP テスト環境、`wp_mail` は filter で bool 差し替え可 — サイト HTTPS 不要)
* [ ] ステージング (HTTPS): フォーム ID → `inquiry_type` 解決 + 種別ごと `DestinationRecord`
* [ ] ステージング (HTTPS): `validate_inquiry_dispatch` で deficiencies 非空なら送信せず入力復元
* [ ] ステージング (HTTPS): 管理者 Mail 成功前に完了画面を出さない
* [ ] ステージング (HTTPS): `wp_mail` ヘッダー `Content-Type: {material.content_type}; charset=UTF-8`
* [ ] ステージング (HTTPS): `map_dispatch_result` + ログ (`correlation_id` / `failure_code`、PII なし)
* [ ] ステージング (HTTPS): 管理者 Mail 成功後、自動返信のみ失敗しても完了画面を取り消さない
* [ ] ステージング (HTTPS): 受信箱への到達確認 (SMTP / 転送設定に依存 — `wp_mail` true でも未到達があり得る)

#### 外部結合 (将来) {#status-external-saas}

* [ ] SaaS `kind` 追加後: ステージング HTTPS から実 API 送信 + `map_dispatch_result` (`http_status` / `external_ticket_id`)
* [ ] プラグイン: credentials / エンドポイントを環境変数・定数から渡し、ログに載せない ([usage_spec.md](./interfaces/usage_spec.md))

## 補足

* 本ライブラリは **`wp_mail` を呼ばない**。Mail 成功はプラグインが渡す **bool** を `map_dispatch_result` が写像するだけ ([testing_spec.md](./testing_spec.md))。
* 初版に Zoom / SaaS HTTP アダプタは含めない ([implementation.md](./implementation.md#フェーズ-3--saas-未定))。
* **実装％** は、各§チェックリストの `[x]` / 全項 (仕様分割は除く。Packagist / プラグイン HTTPS § は表記どおり)。
* **非 HTTPS** § がフェーズ1 ([implementation.md §フェーズ1](./implementation.md#フェーズ-1--本-repo-wp-なし)) の受け入れ。**HTTPS** § はフェーズ2 (別 repo) + 運用確認。
* ローカル品質ゲート: `npm run quality:php` (validate → dist → PHPUnit → PHPStan → PHPCS)。
