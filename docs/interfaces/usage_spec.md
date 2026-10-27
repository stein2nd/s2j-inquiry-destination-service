# S2J Inquiry Destination Service - 使用方法

対象読者: **S2J Inquiry Destination** プラグイン実装者。

## 典型フロー (Mail 初版)

```php
use function S2J\InquiryDestinationService\normalize_inquiry_payload;
use function S2J\InquiryDestinationService\validate_inquiry_dispatch;
use function S2J\InquiryDestinationService\build_dispatch_material;
use function S2J\InquiryDestinationService\map_dispatch_result;

// 1. プラグイン: SMF 送信 → ペイロード + 種別解決済み destination
$payload = [
    'inquiry_type' => 'sales',
    'name'         => $posted_name,
    'email'        => $posted_email,
    'phone'        => $posted_phone,
    'message'      => $posted_message,
    'subject_hint' => '',
    'locale'       => determine_locale(),
    'meta'         => [],
];

$destination = [
    'kind'             => 'mail',
    'inquiry_type'     => 'sales',
    'mail_to'          => $resolved_mail_to, // 設定から。空なら送らない
    'saas_endpoint'    => '',
    'saas_project_id'  => '',
    'credentials'      => [],
];

// 2. 検証
$validated = validate_inquiry_dispatch($payload, $destination);
if ($validated['deficiencies'] !== []) {
    // フォームに失敗を返す。入力値はプラグインが欄に戻す
    return;
}

// 3. 材料
$built = build_dispatch_material($validated['payload'], $validated['destination']);
$material = $built['material'];

// 4. プラグイン Runner: wp_mail — §Mail Runner 参照
$headers = [
    sprintf('Content-Type: %s; charset=UTF-8', $material['content_type']),
    sprintf('From: %s', $plugin_from_address),
    sprintf('Reply-To: %s', $validated['payload']['email']),
];
$sent = wp_mail(
    $material['to'],
    $material['subject'],
    $material['body'],
    $headers
);

// 5. 結果 (ログは PII なしのみ — service_spec)
$result = map_dispatch_result('mail', $sent, [
    'inquiry_type' => $validated['payload']['inquiry_type'],
    'correlation_id' => $plugin_correlation_id,
]);

if (!$result['success']) {
    // 完了画面に進まない
    return;
}

// 6. 成功後のみ完了画面 (自動返信は別処理。失敗しても成功扱い)
```

## Mail Runner (`wp_mail` 呼び出し契約)

Service は [MailMaterial](../core/mail_adapter_spec.md) の4キーのみ返します。Runner が **追加で付与** する項目の SoT は本節です (Transport 全体: [transport_boundary_spec.md](../core/transport_boundary_spec.md))。

`wp_mail` の第4引数 `$headers` は **文字列のリスト** です。Service は `$headers` を返しません。

| 項目 | 供給元 | 備考 |
| --- | --- | --- |
| `$to`, `$subject`, `$message` | `MailMaterial` | `build_dispatch_material` の戻り |
| `Content-Type` + `charset=UTF-8` | 材料 `content_type` + Runner | 下記チェックリスト |
| `From` | プラグイン | サイト / ブランド方針。材料に **含めない** |
| `Reply-To` | プラグイン | 推奨: 送信者 `email` (管理者が返信しやすくする) |
| 添付 | プラグイン | 初版は通常なし。材料に **含めない** |
| DKIM / SPF / DMARC | プラグイン + ホスト | [mail_deliverability_spec.md (草案)](https://github.com/stein2nd/s2j-inquiry-destination/blob/main/docs_mod/mail_deliverability_spec.md) |

## プラグインが持つもの (本ライブラリ外)

* フォーム ID → `inquiry_type` slug (管理画面の種別カタログ)
* 種別 slug ごとの `mail_to` 設定 → **宛先レコード組立**
* カスタム slug 送信時は **`subject_hint` に表示名** (i18n) を渡す ([mail_adapter_spec.md](../core/mail_adapter_spec.md))
* 環境変数 / 定数 / オプションの API キー (SaaS)
* `error_log` (時刻、HTTP ステータス・コード、`failure_code`、`correlation_id` 等。PII なし)

## プラグイン実装チェックリスト (Mail 初版)

* [ ] SMF 送信を統一ペイロードに翻訳した
* [ ] フォーム ID から `inquiry_type` を解決した
* [ ] その種別用の `DestinationRecord` を1件渡した (タイプ別設定の参照はプラグイン内)
* [ ] `validate_inquiry_dispatch` で deficiencies 非空なら送信せず、入力欄を復元した
* [ ] 成功前に完了画面 / 受け付け完了メッセージを出さない
* [ ] `wp_mail` のヘッダーに `Content-Type: {material.content_type}; charset=UTF-8` を含めた
* [ ] `From` / `Reply-To` を Runner で付与した (Service 材料外 — § Mail Runner)
* [ ] `map_dispatch_result` に `correlation_id` を渡し、結果をログに残した (PII なし)
* [ ] 管理者宛の成功後、自動返信だけが失敗しても、完了画面を取り消さない

正規フロー: [concept.md §処理フロー](../concept.md#処理フロー-論理)

## 典型フロー (SaaS — Backlog 例)

手順1–2 (ペイロード、`validate_inquiry_dispatch`) は Mail と同型です。宛先キーは [destination_spec.md](../core/destination_spec.md) どおり **すべて** 渡します。

Asana は `kind` → `saas_asana`、`credentials.access_token`、`map_dispatch_result` 第1引数 → `saas_asana` ([saas_asana_adapter_spec.md](../core/saas_asana_adapter_spec.md))。

```php
$destination = [
    'kind'            => 'saas_backlog',
    'inquiry_type'    => 'sales',
    'mail_to'         => [],
    'saas_endpoint'   => 'https://example.backlog.com',
    'saas_project_id' => '42',
    'credentials'     => [
        'api_key'        => $runtime_api_key,
        'issue_type_id'  => $plugin_issue_type_id,
        'priority_id'    => $plugin_priority_id,
    ],
];

$validated = validate_inquiry_dispatch($payload, $destination);
if ($validated['deficiencies'] !== []) {
    return;
}

$built = build_dispatch_material($validated['payload'], $validated['destination']);
$material = $built['material'];

$response = wp_remote_request($material['url'], [
    'method'  => $material['method'],
    'headers' => $material['headers'],
    'body'    => $material['body'],
    'timeout' => 30,
]);

$http_status = (int) wp_remote_retrieve_response_code($response);
if (is_wp_error($response)) {
    $http_status = 0;
}

$raw_body = is_wp_error($response) ? '' : wp_remote_retrieve_body($response);
$parsed   = json_decode($raw_body, true);
if (! is_array($parsed)) {
    $parsed = [];
}

$result = map_dispatch_result('saas_backlog', [
    'http_status' => $http_status,
    'parsed_body' => $parsed,
], [
    'inquiry_type'   => $validated['payload']['inquiry_type'],
    'correlation_id' => $plugin_correlation_id,
]);
```

## プラグインが持つもの (SaaS)

* Backlog: スペース URL、API キー、課題種別 ID / 優先度 ID → **`credentials` に載せる**
* Asana: PAT、`saas_project_id` (種別ごと GID1件)
* `http_status` 0 (通信エラー) → `saas_http_error` ([data_dictionary.md](../contracts/data_dictionary.md))
* レスポンス body のログ出力禁止 (service_spec 境界)

## プラグイン実装チェックリスト (SaaS v0.3)

* [ ] DestinationRecord の **全キー** を渡した
* [ ] 秘密情報は材料のみ。結果・ログに載せない
* [ ] Backlog: クエリー `apiKey=` 禁止 (ヘッダーのみ)
* [ ] deficiencies 非空なら HTTP しない
* [ ] JSON decode 失敗 → `parsed_body: []`
* [ ] `external_ticket_id` が取れるまで完了画面を出さない
* [ ] `map_dispatch_result` に `inquiry_type` と `correlation_id` を渡す

## Composer

S2J Inquiry Destination **v0.2.0** 以降は `^0.4.0` を要求します ([transport_boundary_spec.md §バージョン・依存](../core/transport_boundary_spec.md#バージョン依存))。

```bash
composer require s2j/inquiry-destination-service:^0.4.0
```

## 関連

* 公開面: [php_api_spec.md](./php_api_spec.md)
* プラグイン索引: [s2j-inquiry-destination docs_mod](https://github.com/stein2nd/s2j-inquiry-destination/blob/main/docs_mod/specs.md)
