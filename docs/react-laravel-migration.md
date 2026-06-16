# React + Laravel 移行ロードマップ

> **目的**: 現行の Laravel 12 + Blade モノリスを、**モノレポ分割（`api/` = Laravel API、`frontend/` = React SPA）** へ全面移行する。
> 本ドキュメントは「目標」と「手順表」を定義し、**各セッションで未完了タスクを1つずつ進める**ための単一の進捗管理表とする。

---

## 1. ゴール（最終状態）

- [ ] リポジトリが `api/`（Laravel・JSON API 専用）と `frontend/`（React + Vite + TypeScript SPA）に分割されている
- [ ] 認証は Laravel Sanctum のトークン方式（SPA がトークンを保持）
- [ ] 旧 Blade ビュー（84 ファイル）の全画面が React コンポーネントへ置き換わっている
- [ ] 旧 `resources/views`・Blade 用フロント資産（Bootstrap/jQuery/Vite blade 入力）が削除されている
- [ ] CI / Docker / デプロイがモノレポ構成（バックエンド・フロント別ビルド）に更新されている
- [ ] README とドキュメントが新構成を反映している

### アーキテクチャ決定（確定事項）

| 項目 | 決定 |
| --- | --- |
| 構成 | モノレポ（`api/` + `frontend/`） |
| バックエンド | Laravel 12、JSON API のみ。Blade は段階的に削除 |
| 認証 | Sanctum Personal Access Token（`auth:sanctum`）。フロントは `Authorization: Bearer` |
| フロント | React 18 + TypeScript + Vite + React Router + Axios + TanStack Query（データ取得） |
| UI | 既存デザイン踏襲（サイドバー＋テーブル中心）。CSS は段階移植 |
| API 規約 | `/api/...`、レスポンスは `{ data: ... }` 包み、エラーは JSON（422/401/403/500） |

---

## 2. フェーズ別 手順表（マスターチェックリスト）

各行が「1セッションで進められる粒度」を目安。`状態`: ☐未着手 / ◐進行中 / ☑完了。

### Phase 0 — モノレポ化（土台）

| # | タスク | 状態 | 備考 |
| --- | --- | --- | --- |
| 0-1 | Laravel 一式を `api/` へ移設 | ☑ | `git mv` 済 |
| 0-2 | ルート `.gitignore`（モノレポ用）作成 | ☑ | |
| 0-3 | `docker-compose.yml` を `api/` 参照へ更新＋`frontend` サービス追加 | ☑ | Vite 用 5173 ポート |
| 0-4 | `api/` 単体で `composer install` → `php artisan test` が通ることを確認 | ☑ | **232 passed**。失敗3件は移設前から存在する `ContactsTest`（`contact_id`→`id` リファクタにテスト未追従）で移設とは無関係。検証中に PSR-4 不整合 `keyword.php`→`Keyword.php` を修正 |
| 0-5 | CI ワークフロー（`.github/workflows/*`）のパスを `api/` 基準へ更新 | ☑ | run-tests / code_analysis / code_security / code_build を `working-directory: api`・キャッシュ/DBパス更新。`deploy-to-prod.yml` はモノレポ向けデプロイ再設計が必要なため **Phase 4-5 へ委譲** |

### Phase 1 — バックエンド API 基盤

| # | タスク | 状態 | 備考 |
| --- | --- | --- | --- |
| 1-1 | `User` に `HasApiTokens` 追加 | ☑ | Sanctum |
| 1-2 | `Api\AuthController`（login / me / logout） | ☑ | トークン発行 |
| 1-3 | `routes/api.php` に認証＋保護ルートを登録 | ☐ | `auth:sanctum` グループ |
| 1-4 | CORS / Sanctum 設定をフロントのオリジンに合わせる | ☐ | `config/cors.php`, `SANCTUM_STATEFUL_DOMAINS` |
| 1-5 | 例外を API では JSON で返すよう調整（419 リダイレクト除去等） | ☐ | `bootstrap/app.php` |
| 1-6 | `admin` ミドルウェアを API ルートでも利用可能に | ☐ | 既存 alias 流用 |

### Phase 2 — フロント基盤（`frontend/`）

| # | タスク | 状態 | 備考 |
| --- | --- | --- | --- |
| 2-1 | Vite + React + TS プロジェクト初期化（`package.json`, `tsconfig`, `vite.config.ts`） | ☐ | |
| 2-2 | Axios クライアント（baseURL, トークン注入, 401 ハンドリング） | ☐ | `src/lib/api.ts` |
| 2-3 | 認証コンテキスト＋トークン永続化（localStorage） | ☐ | `src/auth/` |
| 2-4 | React Router 設定＋認証ガード（ProtectedRoute） | ☐ | `src/router.tsx` |
| 2-5 | 共通レイアウト（サイドバー／ヘッダー／フッター）移植 | ☐ | `layouts/sidebar.blade.php` 参照 |
| 2-6 | 共通 UI（テーブル, モーダル, トースト, ローディング, アラート） | ☐ | sweetalert2/toastr 相当を選定 |
| 2-7 | TanStack Query 導入＋エラーハンドリング共通化 | ☐ | |

### Phase 3 — 画面移行（ドメイン別）

> 詳細は §3 の画面対応表。各ドメインを1セッション以上で進める。

| # | ドメイン | 状態 | 主担当画面数 |
| --- | --- | --- | --- |
| 3-1 | 認証（ログイン／パスワード／メール認証） | ☐ | 7 |
| 3-2 | ダッシュボード | ◐ | 1（API済・UI未） |
| 3-3 | 在庫（数量管理／個別管理／端末詳細／バーコード） | ◐ | 7（一部API済） |
| 3-4 | 端末登録（単体／CSV一括／確認） | ☐ | 5 |
| 3-5 | データ（スペック／ベンチマーク／企業／担当者） | ☐ | 8 |
| 3-6 | 手続き・レンタル（カート／CSV／一括返却） | ☐ | 7 |
| 3-7 | 手続き・販売（カート／CSV） | ☐ | 6 |
| 3-8 | 履歴（レンタル／販売／詳細） | ☐ | 5 |
| 3-9 | 設定（ユーザー管理／カテゴリ／カスタムフィールド／メール・CRM同期） | ☐ | 6 |
| 3-10 | 共通コンポーネント・モーダル群 | ☐ | 14 |
| 3-11 | エラーページ（400/404/500/503） | ☐ | 4 |

### Phase 4 — 仕上げ・撤去

| # | タスク | 状態 | 備考 |
| --- | --- | --- | --- |
| 4-1 | 旧 Blade（`resources/views`）と Blade 用フロント資産を削除 | ☐ | 全画面移行完了後 |
| 4-2 | `routes/web.php` を SPA フォールバック or 認証外のみに簡素化 | ☐ | |
| 4-3 | 不要 npm 依存（bootstrap/jsbarcode/toastr 等）を整理 | ☐ | React 版へ置換後 |
| 4-4 | CI にフロントのビルド／型チェック／lint を追加 | ☐ | |
| 4-5 | デプロイ手順（`deploy-to-prod.yml`）をモノレポ対応へ | ☐ | |
| 4-6 | README・docs 更新、`docs/Architecture` 反映 | ☐ | |

---

## 3. 画面対応表（Blade → React）

凡例 — 状態: ☐未 / ◐進行中 / ☑完。`API`: 必要なエンドポイント（未実装は新規作成）。

### 3-1 認証
| Blade | React ルート | API | 状態 |
| --- | --- | --- | --- |
| `auth/login` | `/login` | `POST /api/auth/login` | ☐ |
| `auth/passwords/email` | `/password/forgot` | `POST /api/auth/password/email` | ☐ |
| `auth/passwords/reset` | `/password/reset` | `POST /api/auth/password/reset` | ☐ |
| `auth/passwords/confirm` | `/password/confirm` | `POST /api/auth/password/confirm` | ☐ |
| `auth/verify` | `/email/verify` | `GET/POST /api/auth/email/verify` | ☐ |
| `auth/change_email` | `/profile/email` | `POST /api/profile/email/change` | ☐ |
| `auth/register` | `/users/register`（管理者） | `POST /api/users` | ☐ |

### 3-2 ダッシュボード
| Blade | React ルート | API | 状態 |
| --- | --- | --- | --- |
| `dashboard/index` | `/dashboard` | `GET /api/dashboard` ✅実装済 | ◐ |

### 3-3 在庫
| Blade | React ルート | API | 状態 |
| --- | --- | --- | --- |
| `inventory/stocks/index` | `/inventory/stocks` | `GET /api/inventory/stocks` ✅実装済 | ◐ |
| `inventory/units/index` | `/inventory/units/:code` | `GET /api/devices/category/:code` ✅実装済 | ◐ |
| `devices/device_list` | （上記内のテーブル） | 同上 | ☐ |
| `devices/show` | `/devices/:id` | `GET /api/devices/:id` ✅実装済 | ◐ |
| `devices/barcode_print` | `/devices/:id/barcode` | `GET /api/devices/:id/barcode` | ☐ |
| `devices/search_results` | `/devices/search` | `GET /api/devices/search` | ☐ |
| `devices/status_legend`(部品) | コンポーネント | — | ☐ |

### 3-4 端末登録
| Blade | React ルート | API | 状態 |
| --- | --- | --- | --- |
| `register_device/register_device` | `/device/register` | `POST /api/devices` | ☐ |
| `devices/components/register_device` | フォーム部品 | — | ☐ |
| `devices/components/register_device_multi` | `/device/register/multi` | `POST /api/devices/multi/upload` | ☐ |
| `devices/components/register_device_confirm_multi` | 確認ステップ | `POST /api/devices/multi/store` | ☐ |
| `register_device/register_device_confirm_multi` | 確認ステップ | 同上 | ☐ |

### 3-5 データ（ファイル・企業・担当者）
| Blade | React ルート | API | 状態 |
| --- | --- | --- | --- |
| `devices/device_spec_file` | `/device/file/spec` | `GET/POST /api/devices/file/spec` | ☐ |
| `devices/device_benchmark_file` | `/device/file/benchmark` | `GET/POST /api/devices/file/benchmark` | ☐ |
| `client/index` | `/clients` | `GET /api/clients` | ☐ |
| `client/register` | `/clients/register` | `POST /api/clients` | ☐ |
| `client/client_detail` | `/clients/:id` | `GET /api/clients/:id` | ☐ |
| `contacts/lists` | `/contacts` | `GET /api/contacts` | ☐ |
| `contacts/register` | `/contacts/register` | `POST /api/contacts` | ☐ |
| `contacts/detail` | `/contacts/:id` | `GET /api/contacts/:id` | ☐ |

### 3-6 手続き・レンタル
| Blade | React ルート | API | 状態 |
| --- | --- | --- | --- |
| `rental/index` | `/rental` | `GET /api/rental` | ☐ |
| `rental/rental` | `/rental/cart` | `POST /api/rental/store` | ☐ |
| `rental/components/cart` | カート部品 | — | ☐ |
| `rental/components/file` | CSV 部品 | `POST /api/rental/multi/upload` | ☐ |
| `rental/rental_with_file_confirm` | CSV 確認 | `POST /api/rental/multi/store` | ☐ |
| `rental/multi_return_device_confirm` | `/rental/return/:lendId` | `POST /api/rental/multi/return/:lendId` | ☐ |
| `history/checkout`（貸出明細） | `/rental/checkout/:deviceId` | `GET /api/rental/checkout/:deviceId` | ☐ |

### 3-7 手続き・販売
| Blade | React ルート | API | 状態 |
| --- | --- | --- | --- |
| `sales/index` | `/sale` | `GET /api/sale` | ☐ |
| `sales/sales` | `/sale/cart` | `POST /api/sale/store` | ☐ |
| `sales/components/cart` | カート部品 | — | ☐ |
| `sales/components/file` | CSV 部品 | `POST /api/sale/multi/upload` | ☐ |
| `sales/multi_sale_confirm` | CSV 確認 | `POST /api/sale/multi/store` | ☐ |
| `sales/sales_detail`（書込） | `/sale/write/:deviceId` | `GET /api/sale/write/:deviceId` | ☐ |

### 3-8 履歴
| Blade | React ルート | API | 状態 |
| --- | --- | --- | --- |
| `history/all_rental_historys` | `/rental/history` | `GET /api/rental/history` | ☐ |
| `rental/rental_detail` | `/rental/history/:id` | `GET /api/rental/history/:id` | ☐ |
| `history/all_sales_historys` | `/sale/history` | `GET /api/sale/history` | ☐ |
| `sales/sales_detail` | `/sale/history/:id` | `GET /api/sale/history/:id` | ☐ |
| `history/checkout` | 詳細内 | — | ☐ |

### 3-9 設定
| Blade | React ルート | API | 状態 |
| --- | --- | --- | --- |
| `user/index` | `/users` | `GET /api/users` | ☐ |
| `user/register` | `/users/register` | `POST /api/users` | ☐ |
| `user/profile` | `/profile` | `GET /api/profile` | ☐ |
| `device_categories/index` | `/settings/categories` | `GET/POST/PUT/DELETE /api/device-categories` + `reorder` | ☐ |
| `device_fields/index` | `/settings/fields` | `GET/POST/PUT/DELETE /api/device-fields` + `reorder` | ☐ |
| `mailform` | `/settings/mail` | `POST /api/sendmail`, `GET /api/sync/crm` | ☐ |

### 3-10 共通コンポーネント・モーダル
| Blade | React コンポーネント | 状態 |
| --- | --- | --- |
| `layouts/app` / `sidebar` / `footer` | `AppLayout` / `Sidebar` / `Footer` | ☐ |
| `layouts/auth` | `AuthLayout` | ☐ |
| `component/alert` | `<Alert>` | ☐ |
| `component/cart_list` | `<CartList>` | ☐ |
| `component/search_box` / `search_form` | `<SearchBox>` / `<SearchForm>` | ☐ |
| `component/summary_cards` | `<SummaryCards>` | ☐ |
| `component/modal/checkout` | `<CheckoutModal>` | ☐ |
| `component/modal/client_search`(+for_contact) | `<ClientSearchModal>` | ☐ |
| `component/modal/edit_device_info` | `<EditDeviceModal>` | ☐ |
| `component/modal/edit_rental_history` | `<EditRentalModal>` | ☐ |
| `component/modal/edit_sale_history` | `<EditSaleModal>` | ☐ |
| `component/modal/edit_user` | `<EditUserModal>` | ☐ |
| `component/modal/email_change` | `<EmailChangeModal>` | ☐ |
| `component/modal/incart_modal` | `<InCartModal>` | ☐ |
| `component/modal/return_device` | `<ReturnDeviceModal>` | ☐ |

### 3-11 エラーページ
| Blade | React ルート | 状態 |
| --- | --- | --- |
| `errors/400` `404` `500` `503` | `/error/*`・ErrorBoundary | ☐ |

---

## 4. 各セッションの進め方（ワークフロー）

1. **対象を1つ選ぶ**: §2 のチェックリストで、依存が解消された最上位の `☐`/`◐` タスクを選択（原則フェーズ順）。
2. **ブランチで作業**: 指定の開発ブランチ上で実装する。
3. **実装**:
   - バックエンド: `api/` 配下に API コントローラ／ルート／（必要なら）リクエスト・リソースを追加。Blade は壊さない。
   - フロント: `frontend/src` に画面・コンポーネント・API 呼び出しを追加。
   - 旧画面の挙動・バリデーション・権限（admin）を必ず踏襲する。
4. **検証**:
   - バックエンド: `cd api && php artisan test`、対象 API を `curl`/Tinker で確認。
   - フロント: `cd frontend && npm run build`（型・ビルド）、可能なら手動動作確認。
5. **進捗更新**: 本ファイルの該当行を `☑`（完了）／`◐`（一部）に更新し、補足を備考へ。
6. **コミット**: 1タスク=1コミット目安。`feat(react-migration): <画面名> を React 化` のように記述。
7. **次セッションへの申し送り**: 未完事項・詰まった点を §5 に追記。

### 完了の定義（Definition of Done / 画面単位）
- [ ] 対象 API がテスト or 手動で期待どおり応答する
- [ ] React 画面が旧 Blade と同等の表示・操作・権限制御を満たす
- [ ] ローディング／エラー／空状態を処理している
- [ ] 型チェック・ビルドが通る
- [ ] 本ロードマップの該当行を更新済み

---

## 5. 申し送り・メモ（随時追記）

- 2026-06-16: **Phase 0 完了**。Laravel を `api/` へ移設、`docker-compose.yml` 更新、ルート `.gitignore` 追加。
  移設後の健全性確認（0-4）として `composer install`＋`php artisan test` を実施し **232 passed** を確認。
  CI 4 ワークフロー（0-5）を `api/` 基準へ更新。
- 2026-06-16: Phase 1 で `User` に `HasApiTokens`、`Api\AuthController`・`Api\DashboardController`・`Api\InventoryStockController`・`Api\DeviceController` を実装（**`routes/api.php` への登録は未**＝1-3 で実施）。
- 次の推奨タスク: **1-3（API ルート登録）→ 1-4（CORS/Sanctum）→ 2-1（frontend 初期化）**。
- 注意: 認証は当初 Sanctum トークン方式で確定。CSV 一括・バーコード・カメラスキャン・ドラッグ並び替え・グラフは移植難度が高いため、対象ドメインの後半で個別設計する。

### 既知の課題（移設前から存在 / 本移行の前提ではない）
- `tests/Unit/Models/ContactsTest` の3ケースが失敗。直近の「personnel → contact」リファクタで `Contacts` モデルの主キーが `contact_id`→`id`（auto-increment）へ変わった一方、テストが旧仕様（`contact_id` 主キー・非incrementing・fillable に `contact_id`）を期待しているため。**移設とは無関係**。設定（3-5 の担当者画面 / API 化）の際にモデル仕様へ追従させて解消する。
- `tests/Feature/*` の一部は Blade を描画するため `php artisan test` 前に `npm run build`（Vite manifest 生成）が必要。Blade 全廃（Phase 4-1）まではこの前提を維持。
