# S2J Inquiry Destination Service - 設計原則

## 設計意図 (ゴール)

問い合わせ送信の **ルールと材料組立** を、フレームワークと I/O から切り離し、テストと Packagist 配布で再利用できるようにします。

## 方針

[kis-wordpress エコシステム仕様](https://github.com/stein2nd/kis-wordpress/blob/main/docs_mod/specs.md) と同様、**FOP + Clean Coding** を基本とします。Clean Architecture の定型分割 (UseCase 層の大量クラス) は採用しません。

| 借用する原則 | 本ライブラリでの意味 |
| --- | --- |
| 依存の向きは外 → 内 | Core / Interfaces は WordPress、HTTP を知らない |
| 内側はビジネスルール | 正規化、宛先検証、文面組立 |
| 外側は詳細 | プラグインが SMF、秘密情報、実送信 |
| Transport / Email Auth | **Service 非目標**。SoT はプラグイン + ホスト ([core/transport_boundary_spec.md](./core/transport_boundary_spec.md)) |

## 純関数と不変レコード

* 入力は `array<string, mixed>` (DTO) とし、Core 内で意味のあるキーだけを読む。
* 副作用 (ファイル、ネットワーク、グローバル) は Core に置かない。
* アダプタは **種類ごとに関数1つ**。
    * Similarity の Strategy 全体差し替えではなく、**種別ごとに別宛先を同時に使う** ため、プラグインが種別ごとに宛先を渡し、本ライブラリのレジストリは `kind → callable` の表とする ([dispatch_spec.md](./core/dispatch_spec.md))。

## 個人情報

* 送信先 (メール / SaaS チケット) には、氏名・連絡先・本文を **そのまま** 載せる。
* 結果レコードとログ (プラグイン) には、**載せない**。
* 伏字マスクも、結果レコードには、使わない ([result_spec.md](./core/result_spec.md))。

## 拡張

* 新しい `kind` (例: SaaS REST) は **関数を1つ追加** し、dispatch 表に登録する。
* 未実装の `kind` 用の空スタブは置かない。
* SemVer: 不足コードの削除・意味変更は major。コード追加は minor ([php_api_spec.md](./interfaces/php_api_spec.md))。

## 関連

* SoT 一覧: [specs.md §Source of Truth](./specs.md#source-of-truth-sot)
* ガバナンス: [governance/documentation_governance.md](./governance/documentation_governance.md)
