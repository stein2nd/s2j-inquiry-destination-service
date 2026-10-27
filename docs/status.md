# S2J Inquiry Destination Service - 実装状況

本ページは、現状の実装状況を機能単位で一覧します (製品全体の「いま」)。
索引は [specs.md](./specs.md) です。統合見取り図は [service_spec.md](./service_spec.md) です。規則・型の Source of Truth は [core/](./core/payload_spec.md) と [contracts/](./contracts/data_contract_spec.md) です ([governance/documentation_governance.md](./governance/documentation_governance.md) と同じ)。

最終更新: 2026-10-27

## 仕様書 (参照元)

* [specs.md](./specs.md) — 索引
* [service_spec.md](./service_spec.md) — 統合見取り図 (要約。規則の正本ではない)
* [core/](./core/payload_spec.md) — 規則 (SoT)
* [contracts/data_contract_spec.md](./contracts/data_contract_spec.md) — DTO (SoT)
* [interfaces/php_api_spec.md](./interfaces/php_api_spec.md) — 公開面

## 機能一覧

**完了条件列:** 100% 到達用チェックリスト (1行 = PHPUnit ケース1件、CI 1ステップ、または手動確認1件)。`[x]` = 満たし済み、`[ ]` = 未。正本 spec は各§の見出しリンク。

| 機能名 | 実装済み/未実装 | 実装％ | 完了条件 (100%) |
| --- | --- | --- | --- |
| 仕様分割 (`docs/`) | 確定 | — | [§ 仕様分割](#status-spec-docs) |
| Composer スケルトン (`src/` 公開 API) | 実装済み | 100 | [§ Composer](#status-composer) (12/12) |
| ペイロード正規化 | 実装済み | 100 | [§ 正規化](#status-payload) (4/4) |
| 検証 (不足コード一式) | 実装済み | 100 | [§ 検証 Mail](#status-validate) (11/11) + [§ 検証 SaaS](#status-validate-saas) (10/10) |
| Mail アダプタ + Transport 境界 (v0.4) | 実装済み | 100 | [§ Mail](#status-mail) (6/6) + [§ Transport](#status-transport) (2/2) |
| SaaS アダプタ (Backlog / Asana) | 実装済み | 100 | [§ SaaS 材料](#status-saas-material) (3/3) |
| 結果写像 | 実装済み | 100 | [§ 結果 Mail](#status-result) (4/4) + [§ 結果 SaaS](#status-result-saas) (7/7) |
| PHPUnit / PHPStan / PHPCS | 実装済み | 100 | [§ 品質ゲート](#status-quality) (8/8) |
| Packagist 公開 | 実装済み | 100 | [§ Packagist (手動)](#status-packagist-manual) (7/7) |
| プラグインからの require | 未 | 0 | [§ プラグイン (HTTPS・手動)](#status-plugin-manual) (0/8) |

## 100% 完了条件チェックリスト

### 非 HTTPS 環境でテスト可能なこと

ローカルまたは CI 上で、WordPress 本番相当 URL・実 SMTP・ステージング HTTPS なしに完結する項です。正本: [testing_spec.md](./testing_spec.md)、[ci.md](./engineering/ci.md)。

一括実行 (自動): `npm run quality:php` (= `scripts/verify/all.sh` と同順)。ドキュメント: `npm run lint:docs`。

**最新確認 (2026-10-27):** リリース **v0.4.0** ([GitHub Release](https://github.com/stein2nd/s2j-inquiry-destination-service/releases/tag/v0.4.0) / [Packagist v0.4.0](https://packagist.org/packages/s2j/inquiry-destination-service#v0.4.0))。PHPUnit **39** tests / **88** assertions。`npm run quality:php`・`npm run lint:docs` ローカル OK。

#### 自動テストで可能なこと {#status-non-https-auto}

##### 仕様分割 {#status-spec-docs}

* [x] CI: `docs-lint` が `docs/**` 等を lint ([.github/workflows/docs-lint.yml](../.github/workflows/docs-lint.yml))
* [x] 正本が `docs/`、起草は [docs_mod/README.md](../docs_mod/README.md) のみ ([governance/documentation_governance.md](./governance/documentation_governance.md))

##### Composer スケルトン {#status-composer}

正本: [php_api_spec.md](./interfaces/php_api_spec.md)

* [x] Test: `InquiryDestinationServiceTest::testNormalizeTrimsStringsAndCoercesMeta` — 公開 `normalize_inquiry_payload`
* [x] Test: `InquiryDestinationServiceTest::testValidateReturnsStableDeficiencyOrder` — 公開 `validate_inquiry_dispatch`
* [x] Test: `InquiryDestinationServiceTest::testSingleDeficiencyCodes` — Mail 向け deficiency 全8コード (data provider)
* [x] Test: `InquiryDestinationServiceTest::testDestinationEmptyWhenMailToHasOnlyInvalidAddresses` — `destination_empty` (不正のみ)
* [x] Test: `InquiryDestinationServiceTest::testBuildDispatchMaterialProducesMailMaterial` — 公開 `build_dispatch_material`
* [x] Test: `InquiryDestinationServiceTest::testBuildDispatchMaterialThrowsWhenNotValidated` — deficiencies 非空で `InvalidArgumentException`
* [x] Test: `InquiryDestinationServiceTest::testMapDispatchResultForMail` — 公開 `map_dispatch_result` (true / false / `correlation_id`)
* [x] CI: [.github/workflows/php-quality.yml](../.github/workflows/php-quality.yml) — `composer install` → `scripts/verify/all.sh`
* [x] CI: `php-quality` — caret `^8.3` 下限 assert (WordPress 準拠チェック)
* [x] CI: `php-quality` — PHPStan level8
* [x] CI: `php-quality` — PHPCS 警告0 (`composer phpcs`)
* [x] CI: `composer validate-composer` + `composer verify-dist` ([build_and_release.md](./engineering/build_and_release.md))

##### ペイロード正規化 {#status-payload}

正本: [payload_spec.md](./core/payload_spec.md)

* [x] Test: `testNormalizeTrimsStringsAndCoercesMeta` — 文字列 trim
* [x] Test: `testNormalizeTrimsStringsAndCoercesMeta` — `meta` 非配列 → `[]`
* [x] Test: `testNormalizeTrimsStringsAndCoercesMeta` — 未知トップレベルキー除去
* [x] Test: `testValidateReturnsDestinationUnchanged` — `destination` を正規化せず入力どおり返す ([validation_spec.md](./core/validation_spec.md))

##### 検証 (不足コード — Mail) {#status-validate}

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
* [x] Test: `testCustomInquiryTypeSlugIsAccepted` — プラグイン定義 slug (`product_feedback`)

##### 検証 (不足コード — SaaS v0.3) {#status-validate-saas}

正本: [validation_spec.md §評価順](./core/validation_spec.md)

* [x] Test: `SaasInquiryDestinationServiceTest::testBacklogSaasEndpointEmpty` — `destination_saas_endpoint_empty`
* [x] Test: `testBacklogSaasProjectIdEmpty` / `testAsanaSaasProjectIdEmpty` — `destination_saas_project_id_empty`
* [x] Test: `testSaasSingleDeficiencyCodes` — `destination_credentials_api_key_empty`
* [x] Test: `testSaasSingleDeficiencyCodes` — `destination_credentials_issue_type_id_empty`
* [x] Test: `testSaasSingleDeficiencyCodes` — `destination_credentials_priority_id_empty`
* [x] Test: `testSaasSingleDeficiencyCodes` — `destination_credentials_access_token_empty`
* [x] Test: `testUnsupportedKindDoesNotAddSaasDeficiencies` — `slack` 時 SaaS 専用コードなし
* [x] Test: `testAsanaAllowsEmptySaasEndpoint` — Asana + endpoint 空 → 不足なし
* [x] Test: `testBacklogCredentialsNotArrayAddsAllCredentialDeficiencies` — credentials 非 array (Backlog)
* [x] Test: `testSingleDeficiencyCodes` — `destination_kind_unsupported` は `slack` (SaaS 登録後も未登録 kind のみ)

##### Mail アダプタ {#status-mail}

正本: [mail_adapter_spec.md](./core/mail_adapter_spec.md)

* [x] Test: `testBuildDispatchMaterialProducesMailMaterial` — `to` / `subject` / `body` / `content_type`
* [x] Test: `testBuildDispatchMaterialProducesMailMaterial` — `phone` 空時は本文に電話行を出さない
* [x] Test: `testBuildDispatchMaterialProducesMailMaterial` — 件名 `[{inquiry_type}] {subject_hint}`
* [x] Test: `testBuildUsesSubjectFallbackWhenSubjectHintEmpty` / `testRecruitHappyPathValidateAndBuild` — `subject_hint` 空時 slug フォールバック
* [x] Test: `ContractSnapshotTest::testMailSalesBodyMatchesContractFixture` — [tests/Fixtures/contracts/mail_sales_body.txt](../tests/Fixtures/contracts/mail_sales_body.txt)
* [x] Test: `testBuildDispatchMaterialProducesMailMaterial` — Mail 材料キー4つのみ (v0.4 — [transport_boundary_spec.md](./core/transport_boundary_spec.md))

##### Transport 境界 (Mail v0.4) {#status-transport}

正本: [transport_boundary_spec.md](./core/transport_boundary_spec.md)

* [x] Test: Mail `material` キーが `to` / `subject` / `body` / `content_type` のみ (上記 Mail テスト内)
* [x] Doc: `docs/core/transport_boundary_spec.md` + 横断 spec 反映 ([archive/mod-v0.4-transport/](./archive/mod-v0.4-transport/))

##### SaaS 材料 (Backlog / Asana) {#status-saas-material}

正本: [saas_backlog_adapter_spec.md](./core/saas_backlog_adapter_spec.md) / [saas_asana_adapter_spec.md](./core/saas_asana_adapter_spec.md)

* [x] Test: `testBacklogBuildMaterialMatchesMailPlainText` — form + Mail 同一本文
* [x] Test: `testAsanaBuildMaterialJsonDataWrapper` — JSON `data` + `projects` 1件
* [x] Test: `testBacklogBuildMaterialMatchesMailPlainText` — `projectId` / `issueTypeId` / `priorityId` / `summary` / `description`

##### 結果写像 (Mail) {#status-result}

正本: [result_spec.md](./core/result_spec.md)

* [x] Test: `testMapDispatchResultForMail` — `execution_outcome === true` → `success: true`, `failure_code: ""`
* [x] Test: `testMapDispatchResultForMail` — `false` → `mail_rejected`
* [x] Test: `testMapDispatchResultForMail` — `context['correlation_id']` エコー
* [x] Test: `testMapDispatchResultForMail` — Mail 成功時 `external_ticket_id` 空文字、`http_status` `null`

##### 結果写像 (SaaS v0.3) {#status-result-saas}

正本: [result_spec.md §SaaS 写像](./core/result_spec.md)

* [x] Test: `testMapBacklogSuccessNumericId` — `201` + `id` → `external_ticket_id === '1'`
* [x] Test: `testMapBacklogHttpErrors` — `400` / `0` → `saas_http_error`
* [x] Test: `testMapBacklogBodyInvalid` — `201` + id 欠落、`200` + id → `saas_body_invalid`
* [x] Test: `testMapAsanaSuccess` — `parsed_body['data']['gid']`、`correlation_id` エコー
* [x] Test: `testMapBacklogFailureEchoesHttpStatus` — 失敗時 `http_status` エコー
* [x] Test: `testMapSaasThrowsWhenOutcomeInvalid` — outcome 非 array → `InvalidArgumentException`
* [x] Test: `testMapSaasThrowsWhenKeysMissing` — 必須キー欠落 → `InvalidArgumentException`

##### PHPUnit / PHPStan / PHPCS {#status-quality}

正本: [testing_spec.md](./testing_spec.md) / [ci.md](./engineering/ci.md)

* [x] Test: [testing_spec.md](./testing_spec.md) 必須カバレッジ — Unit (**39** tests / **88** assertions)
* [x] Local: `composer phpstan` がエラーなく通る (level8)
* [x] Local: PHPUnit 全緑 (`composer test`)
* [x] Local: PHPCS 警告0 (`composer lint:php` / `composer phpcs`)
* [x] Local: `npm run lint:docs`
* [x] CI: `docs-lint` ([.github/workflows/docs-lint.yml](../.github/workflows/docs-lint.yml))
* [x] CI: `php-quality` — GitHub Actions `main` 成功 ([workflow runs](https://github.com/stein2nd/s2j-inquiry-destination-service/actions/workflows/php-quality.yml))
* [x] Local: `composer validate-composer` + `composer verify-dist` (`npm run quality:php`)

#### 自動テストでは不可能なこと (非 HTTPS) {#status-non-https-manual}

##### Packagist / リリース確認 {#status-packagist-manual}

正本: [build_and_release.md](./engineering/build_and_release.md)

* [x] Packagist: [s2j/inquiry-destination-service](https://packagist.org/packages/s2j/inquiry-destination-service) 登録済み
* [x] Packagist: auto-update — `This package is auto-updated.` を確認 (2026-10-20)
* [x] SemVer: CHANGELOG + タグ `v0.1.0` / `v0.2.0` / `v0.3.0` ([Packagist](https://packagist.org/packages/s2j/inquiry-destination-service))
* [x] SemVer: CHANGELOG `0.4.0` 節 + タグ `v0.4.0` + GitHub Release + [Packagist v0.4.0](https://packagist.org/packages/s2j/inquiry-destination-service#v0.4.0) (2026-10-27 確認)
* [x] README — `composer require ^0.4.0` / PHP v8.3以上 ([README.md](../README.md))
* [x] `composer.json` — `require.php` `^8.3`、`autoload.files` → `src/functions.php`
* [x] Impl: `DestinationKinds` — `mail` / `saas_backlog` / `saas_asana`；`Core/Adapters/Mail` 等 ([implementation.md](./implementation.md))

---

### HTTPS 環境でのみテスト可能なこと

ステージング / 本番 WordPress (HTTPS)、実メール到達、SaaS 実 API、Transport (SMTP / Email Auth) の運用確認です。本 repo の PHPUnit では代替しません。プラグイン正本: [S2J Inquiry Destination](https://github.com/stein2nd/s2j-inquiry-destination) ([usage_spec.md § Mail Runner](./interfaces/usage_spec.md)、[transport_boundary_spec.md](./core/transport_boundary_spec.md))。

#### 自動テストで可能なこと (HTTPS 文脈) {#status-https-auto}

**本 repo:** 該当なし (Core は WP / HTTP / `wp_mail` を呼ばない)。

**別 repo (S2J Inquiry Destination):** 次は PHPUnit + WP テスト環境で自動化可能 (サイト HTTPS は不要なものあり)。

* [ ] プラグイン Unit: SMF 送信 → 統一ペイロード翻訳 (`wp_mail` は filter で bool 差し替え)
* [ ] プラグイン Unit: Runner — `MailMaterial` + `From` / `Reply-To` / `Content-Type` ヘッダー組立 ([usage_spec.md § Mail Runner](./interfaces/usage_spec.md))

#### 自動テストでは不可能なこと (HTTPS) {#status-https-manual}

##### プラグイン結合 (Mail) {#status-plugin-manual}

* [ ] プラグイン: `composer.json` — `s2j/inquiry-destination-service` **`^0.4.0`** を require
* [ ] ステージング (HTTPS): フォーム ID → `inquiry_type` 解決 + 種別ごと `DestinationRecord`
* [ ] ステージング (HTTPS): `validate_inquiry_dispatch` で deficiencies 非空なら送信せず入力復元
* [ ] ステージング (HTTPS): 管理者 Mail 成功前に完了画面を出さない
* [ ] ステージング (HTTPS): Mail Runner — `wp_mail` ヘッダー ([usage_spec.md](./interfaces/usage_spec.md))
* [ ] ステージング (HTTPS): `map_dispatch_result` + ログ (`correlation_id` / `failure_code`、PII なし)
* [ ] ステージング (HTTPS): 管理者 Mail 成功後、自動返信のみ失敗しても完了画面を取り消さない
* [ ] ステージング (HTTPS): 受信箱到達確認 (Transport — `wp_mail` true でも未到達があり得る)

##### 外部結合 (SaaS / Transport) {#status-external-manual}

* [ ] ステージング HTTPS: Backlog / Asana 実 API + `map_dispatch_result` (`http_status` / `external_ticket_id`)
* [ ] ステージング HTTPS: Email Auth (DKIM / SPF / DMARC) — プラグイン [mail_deliverability_spec (草案)](https://github.com/stein2nd/s2j-inquiry-destination/blob/main/docs_mod/mail_deliverability_spec.md)
* [ ] プラグイン: credentials / エンドポイントを環境変数・定数から渡し、ログに載せない ([usage_spec.md](./interfaces/usage_spec.md))

## 補足

* 本ライブラリは **`wp_mail` を呼ばない**。Mail 成功はプラグインが渡す **bool** を `map_dispatch_result` が写像するだけ ([testing_spec.md](./testing_spec.md))。
* **Transport 境界 (v0.4):** Service は **MailMaterial 4キー** まで。SMTP / From / Email Auth はプラグイン Runner ([transport_boundary_spec.md](./core/transport_boundary_spec.md))。
* **実装％** は、各§チェックリストの `[x]` / 全項 (仕様分割は除く)。
* **非 HTTPS** § (自動 + 手動) の Packagist / CI / Unit は **100%** (2026-10-27)。**HTTPS** §はフェーズ2 (別 repo) + 運用 ([implementation.md §フェーズ1](./implementation.md#フェーズ1-本-repo-wp-なし) / [§フェーズ4](./implementation.md#フェーズ4-transport-境界-mail--v040))。
* ローカル品質ゲート: `npm run quality:php` (validate → dist → PHPUnit → PHPStan → PHPCS)。
