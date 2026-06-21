# 開発環境構築手順

本プロジェクトはモノレポ構成です。リポジトリ直下の `docker-compose.yml` が
バックエンド（`api/` = Laravel API）とフロントエンド（`frontend/` = React SPA）の
両サービスをまとめて起動します。

- `api/` … Laravel 12 の JSON API（Sanctum トークン認証）。コンテナ名 `laravel.test`、`http://localhost`
- `frontend/` … React + Vite の SPA。コンテナ名 `frontend`、`http://localhost:5173`

---

## 1. 環境変数ファイルを作成

```bash
# バックエンド（Laravel）
cp api/.env.example api/.env

# フロントエンド（Vite）。VITE_API_BASE_URL の既定は http://localhost
cp frontend/.env.example frontend/.env
```

## 2. コンテナのユーザー/グループ ID を設定

Sail 互換イメージのビルドに必要です（ホストのファイル権限と揃える）。

```bash
export WWWUSER=$(id -u)
export WWWGROUP=$(id -g)
```

## 3. コンテナを起動（初回はイメージビルド）

`api`（PHP/PostgreSQL/Redis/Meilisearch/Mailpit）と `frontend`（Vite dev server）を
まとめて起動します。`frontend` サービスは起動時に `npm install` と `npm run dev` を自動実行します。

```bash
docker compose up -d --build
```

## 4. PHP 依存関係をインストール

```bash
docker compose exec laravel.test composer install
```

## 5. アプリケーションキーを生成

```bash
docker compose exec laravel.test php artisan key:generate
```

## 6. `.env` の DB 設定（既定で PostgreSQL 用に設定済み）

`api/.env.example` は `docker-compose.yml` の PostgreSQL サービスに合わせてあります。
変更が必要な場合のみ以下を確認してください。

```dotenv
DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=secret
```

## 7. マイグレーションとシーダーを実行

```bash
docker compose exec laravel.test php artisan migrate --seed
```

## 8. 動作確認 URL

- API（Laravel）: `http://localhost`
- フロントエンド（React SPA）: `http://localhost:5173`
- Mailpit（メール開発環境）: `http://localhost:8025`
- Meilisearch: `http://localhost:7700`

## 9. フロントエンドを Docker を使わずローカルで動かす場合（任意）

```bash
cd frontend
npm install
npm run dev        # http://localhost:5173

# 品質チェック
npm run lint
npm run typecheck
npm run build
```

## 10. 開発終了時にコンテナを停止

```bash
docker compose down
```

---

## 補足: artisan / composer / テストの実行

Laravel 関連のコマンドは `laravel.test` コンテナ内で実行します。

```bash
docker compose exec laravel.test php artisan <command>
docker compose exec laravel.test composer <command>
docker compose exec laravel.test php artisan test
```

コード品質ツール（Larastan / Pint）の詳しい使い方は
[develop_tips.md](develop_tips.md) を参照してください。
