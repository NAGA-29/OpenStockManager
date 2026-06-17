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
| 1-3 | `routes/api.php` に認証＋保護ルートを登録 | ☑ | `auth:sanctum` グループ。login（公開）/ me・logout・dashboard・inventory/stocks・devices を登録。Sanctum 用 `expires_at` 列追加移行も実施 |
| 1-4 | CORS / Sanctum 設定をフロントのオリジンに合わせる | ☑ | `config/cors.php` に `FRONTEND_URL`(既定 `http://localhost:5173`)を追加。`config/sanctum.php` を公開し `SANCTUM_STATEFUL_DOMAINS` を env 化。`.env.example` 追記 |
| 1-5 | 例外を API では JSON で返すよう調整（419 リダイレクト除去等） | ☑ | `bootstrap/app.php`。`api/*`/`expectsJson` 時は DeviceException・ImageProcessingException を 422 JSON 化、419 リダイレクトは Blade のみに限定 |
| 1-6 | `admin` ミドルウェアを API ルートでも利用可能に | ☐ | 既存 alias 流用 |

### Phase 2 — フロント基盤（`frontend/`）

| # | タスク | 状態 | 備考 |
| --- | --- | --- | --- |
| 2-1 | Vite + React + TS プロジェクト初期化（`package.json`, `tsconfig`, `vite.config.ts`） | ☑ | `frontend/` 作成。Vite5+React18+TS、Router/Axios/TanStack Query を依存に追加。`npm run build`/`typecheck`/`lint` green、dev は 5173 で 200 応答 |
| 2-2 | Axios クライアント（baseURL, トークン注入, 401 ハンドリング） | ☑ | `src/lib/api.ts`＋`src/lib/token.ts`。baseURL=`${VITE_API_BASE_URL}/api`、Bearer 自動付与、401 で token 破棄＋`/login` 誘導 |
| 2-3 | 認証コンテキスト＋トークン永続化（localStorage） | ☑ | `src/auth/`（context/AuthProvider/useAuth/types）。起動時 `me` 復元・`login`/`logout`。`main.tsx` で全体を Provider 包み |
| 2-4 | React Router 設定＋認証ガード（ProtectedRoute） | ☑ | `src/router.tsx`（createBrowserRouter）＋`auth/ProtectedRoute`。`/login`公開・保護下に`/dashboard`・`*`→404。プレースホルダ画面で骨組み |
| 2-5 | 共通レイアウト（サイドバー／ヘッダー／フッター）移植 | ☑ | `AppLayout`/`Sidebar`/`Footer`。`ProtectedRoute`→`AppLayout`→各ページの構成 |
| 2-6 | 共通 UI（テーブル, モーダル, トースト, ローディング, アラート） | ☑ | `components/ui/`。自前実装（外部UIライブラリ不採用） |
| 2-7 | TanStack Query 導入＋エラーハンドリング共通化 | ☑ | `QueryClientProvider` を `main.tsx` に配線。`lib/queryClient.ts` |

### Phase 3 — 画面移行（ドメイン別）

> 詳細は §3 の画面対応表。各ドメインを1セッション以上で進める。

| # | ドメイン | 状態 | 主担当画面数 |
| --- | --- | --- | --- |
| 3-1 | 認証（ログイン／パスワード／メール認証） | ◐ | 7（ログインのみ完了） |
| 3-2 | ダッシュボード | ☑ | API済・UI実装済 |
| 3-3 | 在庫（数量管理／個別管理／端末詳細／バーコード） | ◐ | 7（数量管理／個別管理／端末詳細 完了。バーコード/検索 残） |
| 3-4 | 端末登録（単体／CSV一括／確認） | ◐ | 5（単体登録 完了。画像/CSV一括/確認 残） |
| 3-5 | データ（スペック／ベンチマーク／企業／担当者） | ◐ | 8（クライアント一覧/詳細/登録・担当者一覧/詳細 完了。担当者登録/ファイル 残） |
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
| `auth/login` | `/login` | `POST /api/auth/login` | ☑ |
| `auth/passwords/email` | `/password/forgot` | `POST /api/auth/password/email` | ☐ |
| `auth/passwords/reset` | `/password/reset` | `POST /api/auth/password/reset` | ☐ |
| `auth/passwords/confirm` | `/password/confirm` | `POST /api/auth/password/confirm` | ☐ |
| `auth/verify` | `/email/verify` | `GET/POST /api/auth/email/verify` | ☐ |
| `auth/change_email` | `/profile/email` | `POST /api/profile/email/change` | ☐ |
| `auth/register` | `/users/register`（管理者） | `POST /api/users` | ☐ |

### 3-2 ダッシュボード
| Blade | React ルート | API | 状態 |
| --- | --- | --- | --- |
| `dashboard/index` | `/dashboard` | `GET /api/dashboard` ✅実装済 | ☑ |

### 3-3 在庫
| Blade | React ルート | API | 状態 |
| --- | --- | --- | --- |
| `inventory/stocks/index` | `/inventory/stocks` | `GET /api/inventory/stocks` ✅実装済 | ☑ |
| `inventory/units/index` | `/inventory/units/:code` | `GET /api/devices/category/:code` ✅実装済 | ☑ |
| `devices/device_list` | （上記内のテーブル） | 同上 | ☑ |
| `devices/show` | `/devices/:id` | `GET /api/devices/:id` ✅実装済 | ☑（読取表示。編集は 3-10） |
| `devices/barcode_print` | `/devices/:id/barcode` | `GET /api/devices/:id/barcode` | ☐ |
| `devices/search_results` | `/devices/search` | `GET /api/devices/search` | ☐ |
| `devices/status_legend`(部品) | `<StatusLegend>` | — | ☑ |

### 3-4 端末登録
| Blade | React ルート | API | 状態 |
| --- | --- | --- | --- |
| `register_device/register_device` | `/device/register` | `GET /api/devices/form-options` ✅ + `POST /api/devices` ✅ | ☑（単体。画像は未対応） |
| `devices/components/register_device` | フォーム部品（ページ内に統合） | — | ☑ |
| `devices/components/register_device_multi` | `/device/register/multi` | `POST /api/devices/multi/upload` | ☐ |
| `devices/components/register_device_confirm_multi` | 確認ステップ | `POST /api/devices/multi/store` | ☐ |
| `register_device/register_device_confirm_multi` | 確認ステップ | 同上 | ☐ |

### 3-5 データ（ファイル・企業・担当者）
| Blade | React ルート | API | 状態 |
| --- | --- | --- | --- |
| `devices/device_spec_file` | `/device/file/spec` | `GET/POST /api/devices/file/spec` | ☐ |
| `devices/device_benchmark_file` | `/device/file/benchmark` | `GET/POST /api/devices/file/benchmark` | ☐ |
| `client/index` | `/clients` | `GET /api/clients` ✅実装済 | ☑ |
| `client/register` | `/clients/register` | `POST /api/clients` ✅実装済 | ☑（企業フォーム。担当者同時登録は CRM 前提で対象外） |
| `client/client_detail` | `/clients/:id` | `GET /api/clients/:id` ✅実装済 | ☑（読取。担当者一覧込み） |
| `contacts/lists` | `/contacts` | `GET /api/contacts` ✅実装済 | ☑（読取。担当者名検索込み） |
| `contacts/register` | `/contacts/register` | `POST /api/contacts` | ☐ |
| `contacts/detail` | `/contacts/:id` | `GET /api/contacts/:id` ✅実装済 | ☑（読取） |

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
| `layouts/app` / `sidebar` / `footer` | `AppLayout` / `Sidebar` / `Footer` | ☑ |
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
- 2026-06-16: **1-3 完了**。`routes/api.php` に Sanctum トークン認証ルートを登録。
  公開: `POST /api/auth/login`。`auth:sanctum` 保護: `GET /api/auth/me`・`POST /api/auth/logout`・`GET /api/dashboard`・`GET /api/inventory/stocks`・`GET /api/devices/category/{code}`・`GET /api/devices/{deviceId}`。
  既存の `personal_access_tokens` 移行（2019 年・tokenable_id 文字列化のカスタム版）に Sanctum v4 が要求する `expires_at` 列が無くトークン発行が失敗したため、既存定義を壊さない追加移行を作成。
  `tests/Feature/Api/ApiRoutesTest`（login 成否・401 ガード・認証済みアクセス）を追加し **9 件 green**。全体は **241 passed**（既知の ContactsTest 3 件のみ失敗）。
- 2026-06-16: **1-4 完了**。`config/cors.php` の `allowed_origins` に `FRONTEND_URL`（既定 `http://localhost:5173`）を追加（既存の `APP_URL`・`script.google.com` は踏襲）。
  `config/sanctum.php` を公開し `stateful` を `SANCTUM_STATEFUL_DOMAINS`（既定に Vite 5173 を含む）で env 化、`expiration` を `SANCTUM_TOKEN_EXPIRATION` 化。`.env.example` に `FRONTEND_URL`・`SANCTUM_STATEFUL_DOMAINS` を追記。
  `ApiRoutesTest` にフロントオリジンの CORS プリフライト確認を追加し全体 **242 passed**（既知の ContactsTest 3 件のみ失敗）。
  補足: 認証は Bearer トークン方式が主のため stateful Cookie は現状必須でないが、将来切替に備え設定済み。
- 2026-06-16: **1-5 完了**。`bootstrap/app.php` の例外レンダリングを API 対応化。
  `$request->is('api/*') || expectsJson()` の場合、`DeviceException`・`ImageProcessingException` を `redirect()->back()` でなく **422 JSON（`message`/`context`）** で返す。419 のログインリダイレクトは Blade のみに限定（API はトークン方式のため JSON のまま）。
  Blade 側の既存挙動（redirect back / login）は `HandlerTest` 既存ケースで維持を確認、API JSON ケースを追加し全体 **244 passed**（既知の ContactsTest 3 件のみ失敗）。
- 次の推奨タスク: **1-6（admin ミドルウェアを API でも利用可）→ 2-1（frontend 初期化）**。
  - 補足: `admin` alias は `bootstrap/app.php` で登録済み・`AdminMiddleware` は `$request->user()->isAdmin()` 判定でガード非依存のため、1-6 は「admin 必須の API ルートを実際に追加する時に `->middleware('admin')` を付与する」運用で足りる見込み（現状 admin 専用 API ルート未追加）。Blade 同等の admin 画面（users 等）の API 化時に適用すること。
- 注意: 認証は当初 Sanctum トークン方式で確定。CSV 一括・バーコード・カメラスキャン・ドラッグ並び替え・グラフは移植難度が高いため、対象ドメインの後半で個別設計する。

- 2026-06-16: **2-1 完了**。リポジトリ直下に `frontend/`（Vite5 + React18 + TypeScript）を作成。
  構成: `package.json`（`dev`/`build`/`preview`/`lint`/`typecheck` スクリプト）・`tsconfig.json`（strict・`@/*`→`src/*` エイリアス）・`vite.config.ts`（`server.host=true`/`port=5173`/`strictPort`・`@` エイリアス）・`index.html`・`src/main.tsx`・`src/App.tsx`（プレースホルダ、`VITE_API_BASE_URL` 表示）・`src/index.css`・`src/vite-env.d.ts`（env 型）・`.eslintrc.cjs`・`.env.example`・`.gitignore`。
  依存に **React Router / Axios / TanStack Query** を追加済み（2-2〜2-4 で利用）。`npm install`（外部レジストリ到達 OK）→ `npm run typecheck` / `npm run build` / `npm run lint` すべて green、`npm run dev` で 5173 が HTTP 200 応答を確認。`package-lock.json` も追跡（再現性のため）。
- 2026-06-16: **2-2 完了**。`frontend/src/lib/api.ts`（共有 Axios インスタンス）と `src/lib/token.ts`（localStorage トークン永続化、キー `osm_token`）を追加。
  baseURL=`${VITE_API_BASE_URL}/api`、リクエスト時に `Authorization: Bearer <token>` を自動付与、401 応答時は token 破棄＋`/login` へ誘導（`/auth/login`・`/auth/me` の 401 はリダイレクトせず呼び出し側で処理）。`typecheck`/`build`/`lint` green（未 import のため現状バンドルからは tree-shake、型検査は通過）。
- 2026-06-16: **2-3 完了**。`frontend/src/auth/` に認証基盤を追加（lint `react-refresh` 対策でファイル分割）。
  `context.ts`（`AuthContext`）・`AuthProvider.tsx`（状態供給）・`useAuth.ts`（参照フック）・`types.ts`（`AuthUser`/`AuthContextValue`）。
  起動時にトークンがあれば `GET /api/auth/me` で復元（失敗時 `clearToken`）、`login()`=`POST /api/auth/login`→`setToken`→user 設定、`logout()`=`POST /api/auth/logout`→`clearToken`。`main.tsx` で全体を `AuthProvider` で包み、`App.tsx` で認証状態を表示。`typecheck`/`build`/`lint` green。
- 2026-06-16: **2-4 完了**。`frontend/src/router.tsx`（`createBrowserRouter`）と `auth/ProtectedRoute.tsx` を追加。
  ルート: `/login`（公開）／`ProtectedRoute` 配下に index→`/dashboard` リダイレクト・`/dashboard`／`*`→404。`main.tsx` を `AuthProvider`＋`RouterProvider` 構成へ変更し、未使用化した `App.tsx` を削除。
  プレースホルダ画面 `pages/`（LoginPage/DashboardPage/NotFoundPage）で骨組みのみ用意（ログインフォーム実体は 3-1、ダッシュボード集計は 3-2）。`ProtectedRoute` は `isLoading` 中ローディング・未認証は `/login` へ `<Navigate replace>`。`api.ts` の 401 リダイレクト先 `/login` と一致。`typecheck`/`build`/`lint` green、dev で `/login`・`/dashboard` が 200。
- 2026-06-16: **2-5 完了**。`frontend/src/layouts/` に `AppLayout.tsx`・`Sidebar.tsx`・`Footer.tsx`＋`layout.css` を追加し、旧 `layouts/app・sidebar・footer.blade.php` の見た目（ダークテーマ＋アクセント `#2c22bd`）を踏襲。
  - 組込み: `router.tsx` で `ProtectedRoute`（認証ガード）→ `AppLayout`（共通レイアウト）→ 各保護ページの 2 段構成に変更。各ページは `AppLayout` の `<Outlet>` に流れる。
  - ナビ: 上部バー（ブランド `OpenStockManager`＋ユーザードロップダウン：マイページ `/profile`・ログアウト `useAuth().logout()`→`/login`）。サイドバーは折りたたみ可（縦長トグル）＋セクション開閉（現在パスを含むセクションは初期展開）。
  - **admin 権限踏襲**: 旧 `web.php` で `admin` ミドルウェア保護のルート（`user.list`・`device_categories.*`・`device_fields.*`）に対応するサイドバー項目（ユーザー／機材カテゴリ／カスタムフィールド）を `useAuth().user.is_admin` で出し分け。`設定 > 外部連携` は非 admin でも表示（プレースホルダ）。
  - 各メニューの遷移先は §3 の React ルートに合わせて設定済み（多くは Phase 3 で未実装のため現状 404。レイアウト骨組みとしては機能）。`DashboardPage` はレイアウト配下に収まるよう `app-shell`/重複ログアウトボタンを除去。`index.html` に FontAwesome v5.15.1 CDN を追加（アイコン表示）。
  - DoD: `cd frontend && npm run typecheck && npm run build && npm run lint` すべて green（lint `--max-warnings 0` 通過）。
- 2026-06-16: **3-1 ログインフォーム 完了**（認証ドメインのうちログイン画面のみ）。`frontend/src/pages/LoginPage.tsx` を実装＋`login.css` 追加。
  - 旧 `auth/login.blade.php` の二分割カード（左：説明アサイド／右：フォーム）を Tailwind 非導入のため素の CSS で再現。email/password 制御入力＋送信中の無効化（`ログイン中…`）。
  - 送信は `useAuth().login(email, password)`。成功時は `isAuthenticated` が true になり画面冒頭の `<Navigate to="/dashboard">` で遷移。
  - **エラー踏襲**: 422 応答の `errors`（`auth.failed` は `errors.email` に入る）をフィールド単位で各入力欄下に表示（`is-invalid` 装飾）。その他の応答エラーは `message`、ネットワーク不通は接続エラーを上部 `login-alert` に表示。
  - DoD: `typecheck`/`build`/`lint` すべて green。
  - 残課題: `remember`（Remember Me）はトークン方式では効果がないため UI から省略（旧 Blade にはチェックボックスあり）。パスワードリセット／メール認証／メール変更／ユーザー登録（3-1 の残り 6 画面）は API 未実装のため未着手。
- 2026-06-16: **2-7 完了**。`frontend/src/lib/queryClient.ts` に `QueryClient`（`retry:1`・`staleTime:30s`・`refetchOnWindowFocus:false`）を定義し、`main.tsx` を `QueryClientProvider`（最外）→ `AuthProvider` → `RouterProvider` 構成へ変更。401 は `lib/api.ts` のインターセプタで処理するため query 側の retry は控えめ。
- 2026-06-16: **3-2 ダッシュボード 完了**。`features/dashboard/useDashboard.ts`（`useQuery` フック＋型）と `pages/DashboardPage.tsx`＋`dashboard.css` を実装し、旧 `dashboard/index.blade.php` を移植。
  - 内容: サマリーカード（貸出中台数／延滞中／期限間近）＋延滞・期限間近の 2 テーブル（レンタルID→`/rental/history/:id` リンク・クライアント・デバイス・返却予定日・超過/残日数）。ローディング／エラー（再読込ボタン）／空状態を処理。
  - **API レスポンス注意**: `GET /api/dashboard` は `{ data: ... }` 包みでなく **フラットなキー**（`lending_count`/`near_deadline`/`overdue`）を返す。各行は `device_count`（台数）のみで個別 device 一覧は含まないため、デバイス列は「N台」表示（旧 Blade の device_id バッジ羅列は API 非対応）。必要なら API 側で device 一覧を足す検討を。
  - DoD: `typecheck`/`build`/`lint` すべて green。
- 2026-06-16: **2-6 完了**。`frontend/src/components/ui/` に共通 UI を自前実装（外部 UI ライブラリ不採用）。`ui.css` に全スタイル＋ユーティリティ（`.page-bar`/`.text-danger`/`.text-warning`/`.text-success`）。
  - `Loading.tsx`（スピナー）・`Alert.tsx`（`info/success/warning/danger`、`AlertVariant` 型 export）・`DataTable.tsx`（ジェネリック `Column<T>` 定義・`render`/`empty`/`rowKey`）・`Modal.tsx`（背景クリック/×/Esc 閉じ）。
  - トースト: `toast/`（`types.ts`/`context.ts`/`useToast.ts`/`ToastProvider.tsx`）。`main.tsx` に `ToastProvider` を配線（`QueryClientProvider`→`ToastProvider`→`AuthProvider`→`RouterProvider`）。`show(message, variant)` で右上に表示・4 秒で自動消去。
  - dogfooding: `DashboardPage` を `Loading`/`Alert`/`DataTable` 利用へリファクタ（`dashboard.css` から重複テーブル/状態スタイルを削除）。lint の型 export（`Column`/`AlertVariant`）は `react-refresh/only-export-components` を通過。
- 2026-06-16: **3-3 数量管理 完了**（在庫ドメインの 1 画面目）。`features/inventory/useStocks.ts`＋`pages/InventoryStocksPage.tsx`、ルート `/inventory/stocks` を追加。
  - 旧 `inventory/stocks/index.blade.php` は「開発中」プレースホルダだが `GET /api/inventory/stocks` が実データ（`{ data: [...] }` 包み・`location`/`item_name`/`quantity`/`min_stock`/`below_min`）を返すため、実テーブルとして実装。`below_min` を赤字＋状態列で強調。ローディング/エラー/空を共通 UI で処理。
  - DoD: `typecheck`/`build`/`lint` すべて green。
- 次の推奨タスク: **3-3 の残り（個別管理 `/inventory/units/:code`＝`GET /api/devices/category/:code`、端末詳細 `/devices/:id`＝`GET /api/devices/:id`）→ 3-2/3-3 で作った feature パターンを横展開**。
  - 3-3 残メモ: 個別管理は category code（STB 等）でデバイス一覧、端末詳細は device 単体。`useStocks` と同じ要領で `features/inventory/` に query フックを追加し `DataTable` で一覧化。端末詳細は編集モーダル（旧 `edit_device_info`）が絡むため、まず読み取り表示→編集は Phase 3-10 のモーダルと合わせて。バーコード/検索は移植難度が高く後回し（§注意参照）。
  - 共通 UI 活用: 一覧は `DataTable`、フィードバックは `useToast`（mutation 成功/失敗時）、確認ダイアログは `Modal` を利用する。
  - 2-5 残課題: 上部バーのカートボタン（旧 `InCartModal`）は Phase 3-10、ナビのドロップダウン外側クリック閉じは未実装。
  - 3-1 残課題: パスワードリセット／メール認証／メール変更／ユーザー登録（残 6 画面）は対応 API 未実装のため未着手。
  - **1-6 について**: `admin` alias 登録済み・`AdminMiddleware` はガード非依存。admin 専用 API ルートを足す回（3-9 設定等）で `->middleware('admin')` を付与すれば実質達成。
  - 2-5 残課題（後続で対応）: 上部バーのカートボタン（旧 `InCartModal`）は Phase 3-10 で配線するため未実装。ナビのドロップダウンは外側クリックで閉じる挙動は未実装（トグルのみ）。
  - 3-1 残課題: パスワードリセット／メール認証／メール変更／ユーザー登録（残 6 画面）は対応 API 未実装のため未着手。
  - **1-6 について**: `admin` alias 登録済み・`AdminMiddleware` はガード非依存。admin 専用 API ルートを足す回（3-9 設定等）で `->middleware('admin')` を付与すれば実質達成。
  - **1-6 について**: `admin` alias は `bootstrap/app.php` で登録済み・`AdminMiddleware` は `$request->user()->isAdmin()` 判定でガード非依存。admin 専用 API ルートを足す回（例: 3-9 設定/ユーザー管理の API 化）に `->middleware('admin')` を付与して実質達成すればよく、単独セッションを割く必要は薄い。

- 2026-06-16: **3-3 個別管理＋端末詳細 完了**（在庫ドメインの残り。バーコード/検索を除く）。
  - **API 拡張**（`Api\DeviceController`）: `byCategory` を旧 `device_list` 相当に拡充——`categories`（タブ用）・`current`・`counts`（all/lending/defective、いずれも soft-delete 除外）・行に `note`/`sale_id`/`has_images` を追加。未知カテゴリは 404。`show` を旧 `show` の読取部に拡充——`option`/`using_user_id`/各日付（Y-m-d）/`modified_at`/`images`/`rental_hists`/`sale_hists` と、**定義解決済み** `custom_fields`（`{key,label,type,value,display}`、select はラベル解決・boolean は値そのまま）を返す。共通 `resource()` の `condition` が `condition->name`（存在しない列）を参照していた**既存バグを `condition->condition` に修正**。
  - **フロント**: `features/inventory/useDeviceCategory.ts`・`useDevice.ts`（query フック＋型）、`pages/InventoryUnitsPage.tsx`（カテゴリタブ＝NavLink・サマリーカード・ステータスアイコン・端末IDリンク→詳細）、`pages/DeviceDetailPage.tsx`（情報テーブル＋カスタムフィールド＋画像＋貸出/販売履歴テーブル、読取のみ）、共通 `components/StatusLegend.tsx`（旧 `status_legend` 部品）。`router.tsx` に `/inventory/units/:code`・`/devices/:id` を追加。
  - **サマリーカード**は `dashboard.css` の `.summary-card*` を共有（router で全ページ静的 import のため CSS は単一バンドルに同梱）。
  - 検証: `cd api && php artisan test` → **248 passed**（新規 `tests/Feature/Api/DeviceApiTest` 4 件含む。既知 ContactsTest 3 件のみ失敗、risky 1 は移行前から）。`cd frontend && npm run typecheck && npm run build && npm run lint` すべて green。
  - **未実装（意図的に後回し）**: 端末詳細の操作ボタン（編集モーダル＝3-10／貸出・販売・返却＝3-6/3-7／バーコード印刷）、一覧の一括選択チェックボックス（カート＝3-10）、検索フォーム（カメラ/バーコードスキャン含む）、ページネーション。一覧は現状全件取得（旧 Blade は 10 件ページング）。
- 2026-06-16: **3-4 端末登録（単体）完了**（画像アップロード・CSV 一括は後回し）。
  - **API 追加**（`Api\DeviceController`）: `GET /api/devices/form-options`（カテゴリ＝種別ごとのカスタムフィールド定義込み＋コンディション一覧。`/devices/{deviceId}` より**前**にルート定義）、`POST /api/devices`（単体登録、`device_id` 自動採番）。バリデーションは新規 `StoreDeviceApiRequest`（旧 `StoreDeviceRequest` を踏襲しつつ失敗時は 422 JSON。`defective`/`not_for_sale` は真偽値、`device_serial` は `unique` 追加で 500 でなく 422 に）。保存ロジックは旧 `storeDevice` 準拠。
  - **フロント**: `features/inventory/useDeviceFormOptions.ts`（query）・`useRegisterDevice.ts`（mutation）、`pages/RegisterDevicePage.tsx`（カテゴリ選択で動的カスタムフィールド描画・422 をフィールド単位表示・成功時トースト＋登録 ID バナー＋詳細リンク・フォームリセット）。`router.tsx` に `/device/register` を追加（サイドバー「登録 > 機材」と一致）。
  - 検証: `php artisan test` → **252 passed**（`DeviceApiTest` 計 8 件に。既知 ContactsTest 3 件のみ失敗）。`npm run typecheck && npm run build && npm run lint` すべて green。
  - **未実装（意図的）**: 端末写真アップロード（旧 `device_image`＝ImageProcessor/Storage、難度高につき後回し。UI に「準備中」表記）、`sale_date_at`（旧 `storeDevice` も未保存のため踏襲して省略）、CSV 一括登録／確認（`register_device_multi`／`confirmMulti`＝3-4 残）。コンディション選択肢は conditions テーブル（id 1-4）由来、バリデーションは `DeviceEnum::CONDITIONS`（1-5）由来。
- 2026-06-16: **3-5 クライアント一覧＋詳細 完了**（データ系のうちクライアント読取部）。
  - **API 追加**（`Api\ClientController`）: `GET /api/clients`（`{ data }` 包み・`word` クエリで会社名 like 検索・soft-delete 除外）、`GET /api/clients/:id`（企業情報＋担当者一覧 `contacts` を含む。未知 ID は 404）。
  - **フロント**: `features/clients/useClients.ts`・`useClient.ts`、`pages/ClientsPage.tsx`（検索ボックス＋一覧＋詳細リンク）、`pages/ClientDetailPage.tsx`（企業情報テーブル＋担当者 `DataTable`、CRM 変更前提の注記）。`router.tsx` に `/clients`・`/clients/:id` を追加（サイドバー「データ一覧 > クライアント」と一致）。`device-card`/`device-info-table` 系スタイルは inventory.css を共有。
  - 検証: `php artisan test` → **257 passed**（新規 `ClientApiTest` 5 件。既知 ContactsTest 3 件のみ失敗）。`npm run typecheck && npm run build && npm run lint` すべて green。
  - **未実装（意図的）**: クライアント登録 `POST /api/clients`（`client/register`＝3-5 残）、CRM 同期ボタン（`syncFromCRM`＝3-9 外部連携）、ページネーション（一覧は全件取得・`word` 検索のみ）。担当者の詳細リンクは contacts ドメイン未実装のため未配線（一覧表示のみ）。
- 次の推奨タスク: **3-5 残**（担当者 contacts 一覧/詳細＝`ContactsTest` 既知不整合の解消を兼ねる／クライアント登録／スペック・ベンチマークファイル）。または 3-4 残（CSV 一括）、3-3 残（バーコード/検索）。いずれも「対応 API 実装済みか」を先に確認。CSV 一括・バーコード・カメラスキャンは難度が高いので個別設計回で。
  - **contacts 着手時の注意**: `tests/Unit/Models/ContactsTest` の 3 件失敗は `Contacts` の主キーが `contact_id`→`id`（auto-increment）へ変わったのにテストが旧仕様を期待しているため。contacts の API 化に合わせてモデル仕様（fillable/primaryKey）へテストを追従させて解消すること。

- 2026-06-17: **3-5 担当者（contacts）一覧＋詳細 完了**（データ系の担当者読取部。`ContactsTest` 既知不整合も解消）。
  - **API 追加**（`Api\ContactController`）: `GET /api/contacts`（`{ data }` 包み・`word` クエリで担当者名 like 検索・soft-delete 除外・所属企業名 `company` を併せて返す）、`GET /api/contacts/:id`（担当者詳細＋企業名。未知 ID は 404）。`routes/api.php` に登録。
  - **テスト追従**: `tests/Unit/Models/ContactsTest` の既知 3 件失敗を、`Contacts` モデルの**現仕様**（主キー `id`・incrementing・`UPDATED_AT=modified_at`・fillable に `id`）へテストを追従させて解消（モデルは変更しない）。新規 `tests/Feature/Api/ContactApiTest` 6 件（認証/一覧/soft-delete除外/word検索/詳細/404）を追加。
  - **フロント**: `features/contacts/useContacts.ts`・`useContact.ts`、`pages/ContactsPage.tsx`（検索ボックス＋一覧＋詳細リンク）、`pages/ContactDetailPage.tsx`（担当者情報テーブル＋所属企業リンク、CRM 変更前提の注記）。`router.tsx` に `/contacts`・`/contacts/:id` を追加、`Sidebar` の「データ一覧」へ「担当者」項目を追加。`ClientDetailPage` の担当者行の名前を `/contacts/:id` へリンク配線（従来は未配線）。スタイルは `clients.css`/`inventory.css` を共有（重複定義なし）。
  - 検証: `cd api && php artisan test` → **266 passed / 1 risky**（既知 ContactsTest 3 件が解消、新規 ContactApiTest 6 件を含む。risky 1 は移行前から）。`cd frontend && npm run typecheck && npm run build && npm run lint` すべて green。
  - **未実装（意図的に後回し）**: 担当者登録 `POST /api/contacts`（`contacts/register`＝3-5 残）、クライアント登録（`client/register`）、スペック・ベンチマークファイル画面。CRM 同期は 3-9。一覧は全件取得（ページネーション未実装・`word` 検索のみ）。
- 次の推奨タスク: **3-5 残**（クライアント登録 `POST /api/clients`／担当者登録 `POST /api/contacts`：旧 `StoreClientRequest`・`StoreContactRequest` 参照、Api 版リクエストで 422 JSON 化／スペック・ベンチマークファイル画面）。または 3-4 残（CSV 一括）、3-3 残（バーコード/検索）。いずれも着手前に「対応 API 実装済みか」を確認。CSV 一括・バーコード・カメラスキャンは難度が高いので個別設計回で。

- 2026-06-17: **3-5 クライアント登録 完了**（企業フォーム）。
  - **API 追加**（`Api\ClientController@store`）: `POST /api/clients`（`client_id` を UUIDv7 で自動採番、成功時 201＋作成リソース）。バリデーションは新規 `StoreClientApiRequest`（旧 `StoreClientRequest` を踏襲：`company`/`url`(url)/`tel`(numeric・8〜11桁)/`street_address`/`note`(nullable)。失敗時は 422 JSON）。`routes/api.php` に登録。
  - **フロント**: `features/clients/useRegisterClient.ts`（mutation）、`pages/ClientRegisterPage.tsx`（422 をフィールド単位表示・成功トースト＋詳細リンク・登録後に一覧クエリを invalidate）。`router.tsx` に `/clients/register`（`/clients/:id` より前）を追加、`Sidebar`「登録」へ「クライアント」項目、`ClientsPage` に「新規登録」ボタンを追加。フォーム描画・422 ハンドリングは `RegisterDevicePage` パターンを踏襲。スタイルは `register.css`/`clients.css` を共有。
  - **意図的な対象外**: 旧 `client/register` は担当者（contact）フォームを同梱するが、担当者情報は CRM 連携前提のため企業フォームのみ移植（担当者登録 `POST /api/contacts` は 3-5 残）。`post_code` は旧フォームに無く踏襲して省略。
  - 検証: `cd api && php artisan test` → **269 passed / 1 risky**（`ClientApiTest` に store 3 件追加）。`cd frontend && npm run typecheck && npm run build && npm run lint` すべて green。
- 次の推奨タスク: **3-5 残**（担当者登録 `POST /api/contacts`：旧 `StoreContactRequest` 参照、Api 版で 422 JSON 化・`client_id` の存在検証／スペック・ベンチマークファイル画面＝画像/ファイルアップロードを伴うため難度中）。または 3-4 残（CSV 一括）、3-3 残（バーコード/検索）。着手前に「対応 API 実装済みか」を確認。

### 既知の課題（移設前から存在 / 本移行の前提ではない）
- ~~`tests/Unit/Models/ContactsTest` の3ケースが失敗~~ **2026-06-17 解消済み（3-5 担当者対応でテストを現モデル仕様へ追従）**。直近の「personnel → contact」リファクタで `Contacts` モデルの主キーが `contact_id`→`id`（auto-increment）へ変わった一方、テストが旧仕様（`contact_id` 主キー・非incrementing・fillable に `contact_id`）を期待していたのが原因。
- `tests/Feature/*` の一部は Blade を描画するため `php artisan test` 前に `npm run build`（Vite manifest 生成）が必要。Blade 全廃（Phase 4-1）まではこの前提を維持。
