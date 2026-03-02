# DeviceManager

<img src="public/images/logo.png" alt="DeviceManager" width="200">

在庫管理システム(特定の条件でカスタマイズされた機材管理システム)です。

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
- [Docker](https://www.docker.com/) / [Laravel Sail](https://laravel.com/docs/sail)
- [PHP](https://www.php.net/) ^8.4
- [Laravel](https://laravel.com/) ^12.0
- [PostgreSQL](https://www.postgresql.org/) 14
- [Redis](https://redis.io/)
- [Meilisearch](https://www.meilisearch.com/) v1.8.1
- [Mailpit](https://github.com/axllent/mailpit)（メール開発環境）

### 使用ライブラリ
#### バックエンド
- [League CSV](https://csv.thephpleague.com/) v9.7
- [Sendgrid](https://sendgrid.kke.co.jp/) v7.11
- [Laravel-backup](https://spatie.be/docs/laravel-backup) (spatie/laravel-backup)
- [Sentry](https://sentry.io/) v4.20（エラーモニタリング）
- [Laravel Sanctum](https://laravel.com/docs/sanctum) v4.0

#### フロントエンド
- [JsBarcode](https://lindell.me/JsBarcode/) v3.12.3（バーコード生成）
- [Bootstrap](https://getbootstrap.com/) v5.3.3
- [Tailwind CSS](https://tailwindcss.com/) v3.4.17
- [SweetAlert2](https://sweetalert2.github.io/) v11.6.13
- [Toastr](https://github.com/CodeSeven/toastr) v2.1.4
- [TypeScript](https://www.typescriptlang.org/) v5.7.3
- [Vite](https://vitejs.dev/) v6.0.0

### 開発ツール
- [Larastan](https://github.com/larastan/larastan) v3.0（静的解析）
- [Laravel Pint](https://laravel.com/docs/pint) v1.27（コードフォーマット）
- [PHP CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) v3.8
- [PHPUnit](https://phpunit.de/) v11.0
