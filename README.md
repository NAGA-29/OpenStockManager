# OpenStockManager

<p align="center"><img src="api/public/images/logo.png" alt="OpenStockManager" width="500"></p>

<div align="center">

**在庫管理システム(特定の条件でカスタマイズされた機材管理システム)**

[![Laravel](https://img.shields.io/badge/Laravel-12.0-red.svg)](https://laravel.com/)
[![PHP](https://img.shields.io/badge/PHP-8.4-blue.svg)](https://www.php.net/)
[![React](https://img.shields.io/badge/React-18.3-61dafb.svg)](https://react.dev/)
[![Sail](https://img.shields.io/badge/Sail-Docker-green.svg)](https://laravel.com/docs/sail)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

[デモを見る](#) | [ドキュメント](docs/) | [貢献する](docs/CONTRIBUTING.md)

</div>


### アーキテクチャ
フロントエンドとバックエンドを分離したモノレポ構成です。

- `api/` … Laravel による JSON API（Laravel Sanctum によるトークン認証）。Blade UI は撤去済みで、Web ルートは持たず API 専用。
- `frontend/` … React + TypeScript + Vite による SPA。API を呼び出して画面を描画する。


### 主な機能
- ダッシュボード（貸出中台数・返却期限間近・延滞一覧の表示）
- 各端末情報の登録/閲覧/編集（単体・CSVによる一括登録）
- 機材カテゴリの管理（登録/編集/削除/並び替え）
- バーコード印刷（端末登録時に使用）
- 端末画像・スペックファイル・ベンチマークファイルのアップロード
- 取引企業、担当者の情報を登録/閲覧/編集
- 外部CRMとの連携（取引企業・担当者情報の自動同期）
- 端末の貸出、返却（一括返却対応）および販売の手続きと履歴保持
- CSVファイルによる一括貸出・一括販売
- 返却期限のメール通知（SendGrid）
- 管理者ユーザーの管理（登録/編集/メールアドレス変更）

### 開発環境
#### バックエンド（`api/`）
- [Docker](https://www.docker.com/) / [Laravel Sail](https://laravel.com/docs/sail)
- [PHP](https://www.php.net/) ^8.4
- [Laravel](https://laravel.com/) ^12.0
- [PostgreSQL](https://www.postgresql.org/) 14
- [Redis](https://redis.io/)
- [Meilisearch](https://www.meilisearch.com/) v1.8.1
- [Mailpit](https://github.com/axllent/mailpit)（メール開発環境）

#### フロントエンド（`frontend/`）
- [Node.js](https://nodejs.org/) v22
- [React](https://react.dev/) v18.3
- [Vite](https://vitejs.dev/) v5.4
- [TypeScript](https://www.typescriptlang.org/) v5.6

### 開発環境構築手順

- [docs/wiki/development_setup.md](docs/wiki/development_setup.md)


### 使用ライブラリ
#### バックエンド（`api/`）
- [Laravel Sanctum](https://laravel.com/docs/sanctum) v4.0（API トークン認証）
- [League CSV](https://csv.thephpleague.com/) v9.7
- [Sendgrid](https://sendgrid.kke.co.jp/) v7.11
- [Laravel-backup](https://spatie.be/docs/laravel-backup) (spatie/laravel-backup)
- [Sentry](https://sentry.io/) v4.20（エラーモニタリング）

#### フロントエンド（`frontend/`）
- [React Router](https://reactrouter.com/) v6（ルーティング）
- [TanStack Query](https://tanstack.com/query) v5（サーバー状態管理）
- [Axios](https://axios-http.com/) v1.7（API クライアント）
- [JsBarcode](https://lindell.me/JsBarcode/) v3.12.3（バーコード生成）

### 開発ツール
#### バックエンド（`api/`）
- [Larastan](https://github.com/larastan/larastan) v3.0（静的解析）
- [Laravel Pint](https://laravel.com/docs/pint) v1.27（コードフォーマット）
- [PHP CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) v3.8
- [PHPUnit](https://phpunit.de/) v11.0

#### フロントエンド（`frontend/`）
- [ESLint](https://eslint.org/) v8（Lint）
- `npm run typecheck`（型チェック / `tsc --noEmit`）
- `npm run build`（型チェック付きビルド）
