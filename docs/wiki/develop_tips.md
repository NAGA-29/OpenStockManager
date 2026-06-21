# 開発Tips

> **モノレポでの実行場所**: 以下の PHP 系コマンドは Laravel（`api/`）を対象とします。
> Docker 利用時は `docker compose exec laravel.test <コマンド>`、
> ローカル実行時は `api/` ディレクトリ内で実行してください
> （例: `cd api && composer lint`）。本ページの `sail ...` 表記は
> `docker compose exec laravel.test ...` に読み替えてください。

## コード品質ツール

本プロジェクトでは **Larastan**（静的解析）と **Pint**（コードスタイル修正）を導入しています。

### Larastan（静的解析）

PHPStan に Laravel 固有の解析ルール（Eloquent、ファサード、リレーション等）を追加したツールです。
設定ファイル: `phpstan.neon`（解析レベル 5、対象: `app/` `tests/`）

```bash
# 基本実行
sail artisan phpstan

# 解析レベルを指定して実行（0〜9、数値が大きいほど厳密）
sail artisan phpstan --level=8
```

### Pint（コードスタイル）

Laravel 公式のコードスタイル修正ツールです。PSR-12 をベースに設定しています。
設定ファイル: `pint.json`

```bash
# コードスタイルを自動修正
sail artisan pint

# チェックのみ（修正しない）
sail artisan pint --test

# Git で変更したファイルのみ対象
sail artisan pint --dirty
```

### 一括実行（lint）

Pint のチェックと Larastan の解析をまとめて実行します。

```bash
# チェックのみ（Pint + PHPStan）
sail artisan lint

# Pint の自動修正も含めて実行
sail artisan lint --fix

# Git で変更したファイルのみ対象
sail artisan lint --dirty
```

### Composer スクリプト

Artisan コマンドの代わりに Composer 経由でも実行できます。

```bash
composer phpstan      # PHPStan 実行
composer pint         # Pint 自動修正
composer pint:check   # Pint チェックのみ
composer lint         # Pint チェック + PHPStan 一括実行
```
