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

// 4. プラグイン: wp_mail (From / Reply-To はプラグイン方針)
$content_type_header = sprintf(
    'Content-Type: %s; charset=UTF-8',
    $material['content_type']
);
$sent = wp_mail(
    $material['to'],
    $material['subject'],
    $material['body'],
    [$content_type_header]
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

## プラグインが持つもの (本ライブラリ外)

* フォーム ID → `inquiry_type` オプション
* 種別 (`inquiry_type`) ごとの `mail_to` 設定 → **宛先レコード組立**
* 環境変数 / 定数 / オプションの API キー (将来 SaaS)
* `error_log` (時刻、HTTP ステータス・コード、`failure_code`、`correlation_id` 等。PII なし)

## プラグイン実装チェックリスト (Mail 初版)

* [ ] SMF 送信を統一ペイロードに翻訳した
* [ ] フォーム ID から `inquiry_type` を解決した
* [ ] その種別用の `DestinationRecord` を1件渡した (タイプ別設定の参照はプラグイン内)
* [ ] `validate_inquiry_dispatch` で deficiencies 非空なら送信せず、入力欄を復元した
* [ ] 成功前に完了画面 / 受け付け完了メッセージを出さない
* [ ] `wp_mail` のヘッダーは `material['content_type']` + `charset=UTF-8`
* [ ] `map_dispatch_result` に `correlation_id` を渡し、結果をログに残した (PII なし)
* [ ] 管理者宛の成功後、自動返信だけが失敗しても、完了画面を取り消さない

正規フロー: [concept.md §処理フロー](../concept.md#処理フロー-論理)

## Composer

```bash
composer require s2j/inquiry-destination-service:^0.0.1
```

## 関連

* 公開面: [php_api_spec.md](./php_api_spec.md)
* プラグイン索引: [s2j-inquiry-destination docs_mod](https://github.com/stein2nd/s2j-inquiry-destination/blob/main/docs_mod/specs.md)
