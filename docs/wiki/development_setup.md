# 開発環境構築手順

## 1. 環境変数ファイルを作成

```bash
cp .env.example .env
```

## 2. PHP 依存関係をインストール

```bash
composer install
```

## 3. Sail コンテナを起動

```bash
./vendor/bin/sail up -d
```

## 4. アプリケーションキーを生成

```bash
./vendor/bin/sail artisan key:generate
```

## 5. `.env` の DB 設定を PostgreSQL 用に変更（未設定の場合）

```dotenv
DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=secret
```

## 6. マイグレーションとシーダーを実行

```bash
./vendor/bin/sail artisan migrate --seed
```

## 7. フロントエンド依存関係をインストール

```bash
# 初回または lockfile 不一致時
./vendor/bin/sail pnpm install --no-frozen-lockfile

# 2回目以降（通常運用）
./vendor/bin/sail pnpm install --frozen-lockfile
```

## 8. フロントエンド開発サーバーを起動

```bash
./vendor/bin/sail pnpm run dev
```

## 9. 動作確認 URL

- アプリ: `http://localhost`
- Mailpit: `http://localhost:8025`

## 10. 開発終了時にコンテナを停止

```bash
./vendor/bin/sail down
```
