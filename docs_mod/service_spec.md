# S2J Inquiry Destination Service - サービス仕様 (ドラフト)

採用・合意前の設計メモとして `docs_mod/` に置き、確定後は `docs/SERVICE_SPEC.md` に移行します。

## 概要

本ドキュメントは、S2J Inquiry Destination Service の初期設計における、サービス全体の統合の見取り図を定義します。

## 目的

問い合わせフォームの送信を **統一ペイロード** に正規化し、いまはメール、将来は REST API 付きの問い合わせ SaaS に差し替えるための組立ロジックです。Similarity Service と同型の Composer ライブラリです。

本書でいうコンセント変換です。

```text
[フォーム送信] → [統一ペイロード] → [設定で選んだ送信先]
```

本ライブラリは **WordPress 非依存** です。WP フック、設定画面、HTTP の実行、`wp_mail` の実行は扱いません。

出発点は、MW WP Form が保守停止であり、WordPress DB に問い合わせ案件を無制限に溜めたくない、ということです。

## 非目標 (本ライブラリ)

* `add_action` / フォーム HTML / REST ルートの登録。
* 管理画面での API キー保存の実装 (渡された認証情報でリクエストを組むところまで)。
* 問い合わせページの作成 (それは Snow Monkey Forms)。
* 発行後のチケットの処理状況 (それは SaaS 側の管掌)。
* 公開日 / 更新日、ピン留め一覧 (それぞれ別サービス)。

## 機能 (プロダクト観点)

* WordPress DB に案件データを抱え込まず、REST API を公開している問い合わせ SaaS に **Open 問い合わせチケット** を新規発行する。
    * 種別は少なくとも「営業」「採用」の2種類。
    * 発行後の処理状況は SaaS 側の管掌とする。
* 問い合わせページ自体は Snow Monkey Forms で作る。
* SaaS の具体名は未定。
    * [Kansai WordPress Meetup](https://www.meetup.com/kansai-wordpress-meetup/) での相談では、[Backlog](https://backlog.com/ja/) / [Asana](https://asana.com/ja) / [Jooto](https://www.jooto.com) が候補に挙がった。
        * Jooto は、2027-07-31を以て一般提供の終了がアナウンスされた。
* アダプタとして次を満たす (永続化と実行はプラグイン)。 
    * 問い合わせ SaaS の API キーとエンドポイントを、プラグイン管理画面で保存 / 更新できる。
    * SaaS が決まるまでは、レンタルサーバー上の WordPress から PHPMailer (`wp_mail`) によるメール送信とする。

## Composer ライブラリの理由

要件の見た目は WordPress 機能ですが、層は次に分かれます。

| 層 | 中身 | 置き場 |
| --- | --- | --- |
| 計算 | 問い合わせの正規化 (営業 / 採用)、送信先ポート、メール文面 / REST リクエストの組立、成功・失敗の結果レコード | **本ライブラリ** |
| 副作用 | API キーとエンドポイントの保存、Snow Monkey Forms のフック、`wp_mail` の実行、HTTP 送信 | **S2J Inquiry Destination** (プラグイン) |

SaaS 未定でも、ライブラリに **Destination インターフェース** があれば足ります。最初の実装は Mail アダプタ、確定後に Backlog / Asana / Jooto を足します。Meetup で挙がった3つは設定画面の候補であり、ライブラリの「今すぐ全部実装」ではありません。

プラグイン一本にロジックを置くと、kis-inquiry と他サイトでペイロード組立が複製され、ユニットテストも WP 一式が必要になります。

## 本ライブラリの責務

入力は WP の `$_POST` ではなく、**統一ペイロードと送信先設定のレコード** です。出力は、メール用メッセージまたは HTTP リクエスト材料と、結果レコードです。

| 責務 | 内容 |
| --- | --- |
| 正規化 | 種別 (少なくとも sales / recruit)、氏名、連絡先、本文などを比較可能なレコードにする |
| 送信先ポート | Mail / 将来の SaaS を同じ `send(payload)` で差し替える |
| Mail アダプタ | 宛先、件名、本文、ヘッダーの組立。PHPMailer 自体は呼ばない |
| SaaS アダプタ (将来) | エンドポイント、認証、JSON ボディの組立。curl は呼ばない |
| 結果 | 成功 / 失敗、SaaS 側チケット ID (あれば)、再送してよいか |

API キーはライブラリに埋め込みません。プラグインがオプション (または環境変数) から読み、実行時に渡します。

## プラグインの責務 (境界。詳細はプラグイン仕様)

* Snow Monkey Forms の送信を、統一ペイロードに翻訳する。
* 管理画面で送信先 (いまはメール、将来は SaaS)、エンドポイント、API キーを保存する。
* Mail 時は `wp_mail` (内部の PHPMailer) を実行する。SaaS 時は HTTP を実行する。
* 案件のステータス画面は持たない。WP DB にチケット履歴を無制限に貯めない。

KIS 専用のページ構成や文言は **kis-inquiry** に残してよいです。送信先の差し替えは本 S2J 製品に出し、kis-inquiry は当該プラグインを使う (または薄いラッパーにする) 方が共用しやすいです。kis-core に抱え込まない判断とそろえます。

## 設計方針

本ライブラリは [kis-wordpress エコシステム仕様](https://github.com/stein2nd/kis-wordpress/blob/main/docs_mod/specs.md) と同じく、FOP + Clean Coding を基本とします。Clean Architecture の定型分割は採用しません。

データの中身は純粋関数と不変レコードに閉じます。送信先の差し替えは、クラス階層より高階関数 / 小さなアダプタモジュールで足りるならそちらを選びます。

| 借用する原則 | 本ライブラリでの意味 |
| --- | --- |
| 依存の向きは外 → 内 | 純関数は WordPress / HTTP を知らない |
| 内側はビジネスルール | ペイロード正規化と送信先ポートはフレームワーク非依存 |
| 外側は詳細 | プラグインがフォーム、秘密情報、実際の送信を担当する |

## 関連リポジトリ

呼び出し側の正は **S2J Inquiry Destination** です。必要なら kis-inquiry が当該プラグインを有効化 (または require) します。

| 名称 | 種別 | 役割 |
| --- | --- | --- |
| **本ライブラリ** | Composer | 統一ペイロード、送信先ポート、Mail / 将来 SaaS のリクエスト組立 |
| **S2J Inquiry Destination** (仮) | WP プラグイン | 設定画面、秘密情報、SMF フック、`wp_mail` / HTTP の実行 |
| [kis-wordpress](https://github.com/stein2nd/kis-wordpress.git) / kis-inquiry | モノレポ内プラグイン | KIS の問い合わせページ。送信先ロジックは抱え込まない |
| [kis2026_base](https://github.com/stein2nd/kis2026_base.git) | テーマ | 見た目。フォームの正は SMF + プラグイン + 本ライブラリ |

## 実装順 (KIS エコシステムとの関係)

1. 本 repo でスケルトン、統一ペイロード、Mail アダプタの純関数初版 (PHPUnit で WP なし)。
2. S2J Inquiry Destination プラグインが Composer で require し、設定 UI と `wp_mail` をつなぐ。
3. kis-inquiry (または SMF) からペイロードを渡す。Phase-2最初はメールのみ。
4. SaaS 確定後、該当アダプタを本ライブラリに追加し、プラグイン設定で切り替える。
5. 旧案の `packages/inquiry-destination-service/` 仮置きは、本 repo へ寄せる (モノレポ仕様の更新は kis-wordpress 側)。

## 未決 (次の仕様で確定)

* 種別の識別子 (`sales` / `recruit` 等) と、SMF のフォーム ID の対応をライブラリが持つか、プラグインだけが持つか。
* Mail の宛先をタイプごとに分けるか、1アドレスに件名で区別するか。
* SaaS アダプタを本ライブラリに同梱するか、パッケージを分けるか。
* 送信失敗時の再送キューをライブラリの対象にするか (WP 側のジョブにするか)。
* 個人情報を結果レコードやログにどこまで残すか。

## 改訂履歴

| 日付 | 内容 |
| --- | --- |
| 2026-09-29 | 初版ドラフト。コンセント変換、Composer とプラグインの境界、Mail 先行、営業 / 採用、SaaS 未定 (Backlog / Asana / Jooto 候補) までの合意を記録 |
