# Movie Review App

## 概要
映画のレビュー投稿アプリケーションです。
「観る前の判断材料」「観た後の共感」を提供することを目的としています。

職業訓練校の開発演習成果物をベースに、公開を想定してリファクタリングおよびセキュリティ対策を実施しました。

## 本番環境はこちら
https://portfolio.honda-dev.com/

## 使用技術

### 本番環境
- PHP 8.4
- MariaDB / MySQL
- HTML / CSS
- JavaScript
- XServer

### 開発環境
- XAMPP（PHP 7.4）
- Docker / Docker Compose（LAMP構成）
- MySQL 8.4
- Git / GitHub

## 主な機能
- 会員登録 / ログイン機能
- レビュー投稿 / 編集 / 削除
- 評価（レーティング）機能
- ページネーション
- 管理機能

## 設計方針
- 環境ごとの設定分離（env.php）
- DB接続情報の非公開化（exampleファイル使用）
- ER図を考慮したリレーション設計
- 共通パーツ化による保守性向上

## セキュリティ対策
- CSRFトークン実装
- password_hash / password_verify 使用
- セッション固定攻撃対策
- 入力値バリデーション
- XSS対策（htmlspecialchars）
- 不正パラメータ検証

## 公開ポリシー
- 機密情報（env.php / database.php）は除外
- exampleファイルのみ公開
- 実運用を想定したログ管理とアクセス制御を実装

## 画面イメージ

### 作品一覧
![作品一覧](docs/images/item_list.png)

### 作品詳細
![作品詳細1](docs/images/item_detail1.png)
![作品詳細2](docs/images/item_detail2.png)

### マイページ
![マイページ](docs/images/mypage.png)

## ER図

![ER図](docs/images/er.png)

users・items・reviews を中心とした正規化設計。
外部キー制約によりデータ整合性を担保しています。

## 更新履歴

### 2026/03/05
- feat(footer): Qiitaリンク追加・技術スタック更新（Git / GitHub / Docker）
- docs: README使用技術を本番/開発環境で分離・関連リンクセクション追加

### 2026/02/24
- fix/sec: 返信バリデーション関数の誤りを修正・XSSリスク対応・basename()追加
- refactor: profile_edit_check のUPDATE文をリファクタリング
- chore: コード品質の細かい修正・相対パスを絶対パスに統一
- style: style.cssのリファクタリングとCSS分割・未使用ファイル削除
- sec: images/.htaccessにno_image除外ルールを追加

## 関連リンク

- 技術記事（Docker開発環境構築）: https://qiita.com/honda-dev-jp
- 開発ログ / 学習記録: https://x.com/honda_dev
