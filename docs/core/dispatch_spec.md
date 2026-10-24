# S2J Inquiry Destination Service - 送信ポート

本ドキュメントは、宛先 `kind` から **アダプタ関数** を選ぶ規則を定義します。

## 設計意図

Similarity Service の Strategy は **処理全体を1つ差し替え** ますが、問い合わせは **種別ごとに異なる宛先** を同時に使います。よって、(1) プラグイン: `inquiry_type` → 宛先レコード、(2) 本ライブラリ: `kind` → 関数、という2段の解決です ([concept.md §処理フロー](../concept.md#処理フロー-論理))。

## 責務

* 登録済み `kind` のアダプタを定義すること。
* 将来 `kind` 追加時の SemVer を [php_api_spec.md](../interfaces/php_api_spec.md) に委譲すること。

## 非責務

* HTTP / `wp_mail` の実行。

## レジストリ

未登録 `kind` → 検証で `destination_kind_unsupported`。空実装のスタブは置きません。

| kind | 関数 (内部) | 戻り material 型 |
| --- | --- | --- |
| `mail` | `Core\Adapters\Mail::build_material` | `MailMaterial` ([mail_adapter_spec.md](./mail_adapter_spec.md)) |
| `saas_backlog` | `Core\Adapters\SaasBacklog::build_material` | `HttpRequestMaterial` ([saas_backlog_adapter_spec.md](./saas_backlog_adapter_spec.md)) |
| `saas_asana` | `Core\Adapters\SaasAsana::build_material` | `HttpRequestMaterial` ([saas_asana_adapter_spec.md](./saas_asana_adapter_spec.md)) |

## build_dispatch_material

入力: 正規化済み `payload`、検証済み `destination` (deficiencies 空)。

出力 `material` は **いずれか1型**:

* `MailMaterial` — [mail_adapter_spec.md](./mail_adapter_spec.md)
* `HttpRequestMaterial` — [contracts/data_contract_spec.md](../contracts/data_contract_spec.md)

```text
{
  material: MailMaterial | HttpRequestMaterial,
  deficiencies: []   // 通常空。validate 後のみ build を呼ぶ
}
```

`deficiencies` 非空の入力で build を呼ぶ → `InvalidArgumentException` (プログラマー誤り)。

## map_dispatch_result

| kind | execution_outcome |
| --- | --- |
| `mail` | `bool` (`wp_mail` の戻り) |
| `saas_backlog` / `saas_asana` | `SaasExecutionOutcome` ([php_api_spec.md](../interfaces/php_api_spec.md)) |

写像規則: [result_spec.md](./result_spec.md)。製品別フィールド: [saas_backlog_adapter_spec.md](./saas_backlog_adapter_spec.md) / [saas_asana_adapter_spec.md](./saas_asana_adapter_spec.md)。

## 関連

* Mail: [mail_adapter_spec.md](./mail_adapter_spec.md)
* 結果: [result_spec.md](./result_spec.md)
