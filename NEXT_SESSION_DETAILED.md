# 次セッション引き継ぎ指示書（OpenStockManager React 移行 / Blade 撤去（Phase 4-1/4-2）後）

> このドキュメントは「次の AI セッションが最短で作業に入れること」を目的としています。
> **まず §1 で現状把握 → §2 で読むべきファイルを開く → §3 の手順で実装** の順に進めてください。

---

## §1. 現状（2026-06-19 時点）

### 完了済みフェーズ
| Phase | 内容 | 状態 |
|-------|------|------|
| 3-2 | ダッシュボード | ✅ |
| 3-3 | 在庫（数量/個別/詳細/バーコード/検索） | ✅ |
| 3-4 | 端末登録（単体+CSV一括） | ✅（画像のみ残） |
| 3-5 | データ（クライアント/担当者/ファイル 全8画面） | ✅ |
| 3-6 | レンタル手続き | ✅（カート端末検索の配線も完了） |
| 3-7 | 販売手続き | ✅ |
| 3-8 | 履歴（レンタル/販売 統合ビュー） | ✅ |
| 3-9 | 設定 - ユーザー管理 + 機材カテゴリ + カスタムフィールド（admin） | ✅（メール・CRM は残） |
| 3-11 | エラーページ（400/404/500/503） | ✅ |
| **3-10** | **共通コンポーネント（検索/ページ/カード/フォームモーダル/AuthLayout）** | ◐ **今セッションで大部分完了**（業務モーダルは対応画面実装時に） |
| 3-9残 | メール送信・CRM 同期 / プロフィール（任意） | ☐（外部依存あり）**← 次の候補** |
| 3-1残 | 認証まわりの残画面（パスワードリセット等） | ☐ **← 次の候補** |

### テスト状況
```
API:      344 passed / 1 risky / 0 failures（Blade 撤去で長年の 3 failures を解消）
Frontend: build / typecheck  すべて green
```
※ 1 risky は事前から存在する別件（テスト本体がアサーションを持たない等）。Blade 撤去とは無関係。

### 直近セッションで実装した内容
**Blade 撤去（Phase 4-1 / 4-2）**
- `routes/web.php` を API 専用化（Blade UI ルート・`Auth::routes()` を全撤去、ファイルはコメントのみ残す）。
- Blade コントローラ 12 種（Dashboard/Devices/Clients/Contacts/RentalHists/SalesHists/User/Mailing/InventoryStock/InventoryUnit/DeviceCategory/DeviceTypeField）と `Http/Controllers/Auth/*` を削除。
- `resources/views/**` を削除（**`emails/` のみ保持**＝メール機能用）。
- Blade 依存の Feature テストを削除（LoginTest / DashboardAccessTest / BasicTest）、`AdminMiddlewareTest` を API（`/api/users`）検証に置換。
- 結果: 長年の **3 failures（Blade の Vite manifest 由来）が解消**し全 green（344 passed / 0 failed）。
- 保持: `CustomEmailChangeNotification`・`User::sendEmailChangeNotification`・`emails/` ビュー（メール仕様確定後に使用）。`HomeController`（旧 scaffolding・未配線）は据え置き。

**メール（Mailtrap）有効化の準備のみ**
- `api/.env.example` のメール設定を Mailtrap 用に整備（`MAIL_HOST=sandbox.smtp.mailtrap.io`、`MAIL_PORT=2525`、`MAIL_ENCRYPTION=tls`、from 既定値、資格情報は空のまま）。
  送信機能自体は未確定のため**雛形のみ**。Mailtrap の USERNAME/PASSWORD を `.env` に入れれば config は env 経由で効く（`config/mail.php` は変更不要）。

**レンタル・販売の端末状態バリデーション（API）**
- `App\Traits\ChecksSaleableDevices`（販売済み/貸出中/不良/販売対象外）を `StoreSaleApiRequest`・`StoreSaleMultiApiRequest` の `withValidator` で利用。`SaleApiTest` に 4 ケース追加（12 → 16）。
- `App\Traits\ChecksRentableDevices`（販売済み/貸出中/不良。販売対象外フラグは無視）を `StoreRentalApiRequest`・`StoreRentalMultiApiRequest` の `withValidator` で利用。`RentalApiTest` に 3 ケース追加（12 → 15）。
- devices のデフォルト（sale_id=''/lending_now=''/defective=false/not_for_sale=false）なので既存テストは無影響。

**3-10 AuthLayout**
- `layouts/AuthLayout.tsx`（ログイン画面の 2 カラムカード＝ブランディングパネル＋フォーム枠）を切り出し、`LoginPage` が利用。
  フォーム側は `children`。`login.css` は AuthLayout 側で import。ログイン以外の認証画面でも再利用可能。

**3-10 共通コンポーネント（検索 / ページネーション / サマリーカード / フォームモーダル）**
- `components/ui/SearchBox.tsx`（検索入力＋ボタン）、`Pagination.tsx`（前へ/現在/次へ、1 ページ以下は非表示）、
  `SummaryCards.tsx`（ラベル＋数値カード、variant: primary/danger/success/warning）、
  `FormModal.tsx`（`Modal` ベース。フォーム＋キャンセル/送信フッターを共通化、フィールドは children）を新設。
- 一覧 5 画面（History / SaleHistory / RentalHistory / Users / DeviceSearch）の検索/ページネーションを置換。
  `DashboardPage` のサマリーカードを `SummaryCards` に置換。
  設定 3 画面（`UsersPage` / `DeviceCategoriesPage` / `DeviceFieldsPage`）の編集モーダルを `FormModal` に置換。
- 共通スタイルを `components/ui/ui.css` に集約（`.search-*` / `.summary-card*`）。`summary-card*` は `dashboard.css` から移設。
  `rental.css` 等の `.search-*` 同名定義は残置（無害）。
- フロントのみの変更（API テストは 343 passed のまま）。
- 残: AuthLayout・業務モーダル（CartList / CheckoutModal / ReturnDeviceModal 等、対応画面の実装時に移植）。

**3-11 エラーページ（400/404/500/503）**
- 共通 `pages/errors/ErrorPage.tsx`（+ `error.css`）を 4 ページで共有。認証/共通レイアウト外でも単体表示できる自前シェル。
- `NotFoundPage`(404, 既存をリファクタ) / `errors/BadRequestPage`(400) / `errors/ServerErrorPage`(500) / `errors/ServiceUnavailablePage`(503)。
- router: `/error/400`・`/error/500`・`/error/503` を公開ルートに追加。`ProtectedRoute` に `errorElement: <ServerErrorPage/>` を付与し描画例外を 500 で捕捉。catch-all `*`→404 は従来どおり。
- `lib/api.ts`: 応答 503 で `/error/503` へ自動誘導（401 と同様の window.location 誘導）。
- フロントのみの変更（API 変更なし）。

**3-9 設定 - カスタムフィールド（admin 限定）**
- **API**: `Api\DeviceFieldController`（index/store/update/destroy/reorder）。`DeviceFieldApiTest`（12 tests）
  - `GET/POST /api/device-fields`（`?category=` 絞り込み・`field_types` も返す）、`PUT/DELETE /api/device-fields/{id}`、`POST /api/device-fields/reorder`
  - `field_key` はラベルから `DeviceTypeField::generateFieldKey()` で自動採番（カテゴリ内一意）。`select` 型のみ `options` を保持。
  - 更新ではカテゴリ・`field_key` は変更不可。`sort_order` はカテゴリ内 max+1。削除しても端末 JSON 値は残す。
  - `Store/UpdateDeviceFieldApiRequest`（field_type は `DeviceTypeField::FIELD_TYPES` のキー、options は label/value）
- **フロント**: `features/settings/useDeviceFields.ts`、`DeviceFieldsPage`（カテゴリ別グルーピング表示＋追加フォーム＋編集モーダル＋削除＋カテゴリ内 上下並び替え。`select` 型は選択肢エディタ `OptionsEditor`）、router の `/settings/fields` を `AdminRoute` 配下に。
  - 注: `Str::snake('OS Version')` は `o_s_version` になる（連続大文字）。テストのキー期待値に注意。

**3-9 設定 - 機材カテゴリ（admin 限定）**
- **API**: `Api\DeviceCategoryController`（index/store/update/destroy/reorder）。`DeviceCategoryApiTest`（12 tests）
  - `GET/POST /api/device-categories`、`PUT/DELETE /api/device-categories/{id}`、`POST /api/device-categories/reorder`
  - コード変更時は `devices.device_type` を追従更新（トランザクション）。機材が紐づくカテゴリは削除拒否（422）。
  - ⚠️ `nullable` フィールド（icon）は `safe()->all()` に含まれないため `$safe['icon'] ?? null` で受ける（ハマりどころ）。
  - `StoreDeviceCategoryApiRequest`/`UpdateDeviceCategoryApiRequest`（code は `regex:/^[A-Z0-9_]+$/`・一意）
- **フロント**: `features/settings/useDeviceCategories.ts`、`DeviceCategoriesPage`（一覧＋追加フォーム＋編集モーダル＋削除＋上下ボタンで並び替え）、router の `/settings/categories` を `AdminRoute` 配下に。

**3-9 設定 - ユーザー管理（admin 限定）**
- **API**: `Api\UserController`（index/store/update）。`auth:sanctum` + `admin` ミドルウェアで保護（非 admin は 403）。
  `StoreUserApiRequest`/`UpdateUserApiRequest`（メール一意・パスワード強度・role in[admin,user]）。`UserApiTest`（12 tests）
  - ルート: `GET /api/users`・`POST /api/users`・`PUT /api/users/{id}`（`Route::middleware('admin')->group`）
  - ⚠️ `users.id` は bigint オートインクリメント。旧 `StoreUserRequest` の UUID 採番は現スキーマと不一致なので不採用。
- **フロント**: `features/users/useUsers.ts`（list/create/update）、`UsersPage`（一覧＋検索＋ページング＋編集モーダル）、
  `UserRegisterPage`（登録）、`auth/AdminRoute.tsx`（非 admin は `/dashboard` へ）。router の `/users`・`/users/register` を `AdminRoute` 配下に。
  Sidebar「設定 > ユーザー」は既存（adminOnly）。
- **register.css**: email/password/tel の input を共通スタイル対象に追加、`__hint` クラスと actions の flex/gap を追加。

**3-8 履歴（統合ビュー）**
- **API**: `Api\HistoryController@index`（`GET /api/history?type=all|rental|sale&word=&page=`）。
  RentalHist / SaleHist を共通フォーマット（id/type/company/contact/date/status/note）に正規化・マージし、
  日付降順で 10 件ページング（`Collection::forPage` による手動ページネーション）。`HistoryApiTest`（6 tests）
- **フロント**: `features/history/useHistory.ts`、`pages/HistoryPage.tsx`（種別タブ すべて/レンタル/販売 ＋
  キーワード検索 ＋ ページング。各行は既存 per-type 詳細へリンク）、router に `/history`、Sidebar「履歴 > 全体」追加
- **方針メモ**: 旧 `history/checkout.blade.php` は未完成スキャフォールドのため移植対象外と判断。

**3-7 販売（前段）**
- `Api\SaleController`（返却なし）、Sale 系 FormRequest 3 種、`/sale*` ルート、`Device::$fillable` に `sale_id`、`SaleApiTest`（12 tests）
- フロント: `features/sale/useSale.ts`、`SalePage`/`SaleCartForm`/`SaleFileForm`/`SaleHistoryPage`/`SaleHistoryDetailPage`、`sale.css`
- カート端末検索の未配線バグを `useDeviceSearch`（300ms デバウンス）で修正（rental/sale 両方）

---

## §2. 次セッションで読むべきファイル（Phase 4 残 / メール / 認証残）

### 2-1. 候補タスクと所在
- **Phase 4-3 不要 npm 依存の整理**（外部依存なし・推奨）
  - 旧 Blade で使っていた npm 依存（bootstrap / jsbarcode / toastr / sweetalert2 / jquery 等）が `api/package.json` や `frontend/package.json` に残っていないか確認し、未使用なら削除。
  - Blade ビルド資産（`api/resources/js`・`api/resources/sass`・`api/vite.config.js` の Blade エントリ・`api/webpack.mix.js` 等）も未使用なら整理。
  - ⚠️ 削除前に `grep` で参照ゼロを確認。`frontend/` 側の依存は React で使用中なので触らない。
- **Phase 4-4 CI 整備**（外部依存なし）: フロントの build / typecheck / lint、API の `php artisan test` を CI に追加。
- **3-9 メール/CRM**（外部依存・仕様未確定）: メール仕様が決まってから。接続準備（Mailtrap）は済。
  実装時は `config/mail.php`/`config/services.php`、`app/Notifications/*`、`emails/` ビュー、`Mail::fake()` テスト。Sidebar に `/settings/mail`（adminOnly）リンクあり。
- **3-1 認証残**（パスワードリセット等）: メール送信が絡むため仕様確定後。`AuthLayout` が使える。
- **業務モーダル**（未移植の業務画面＝端末編集/カート確定/返却 とセット）: 当該機能を作る時に `Modal`/`FormModal` ベースで実装。
- **済**: 端末状態バリデーションはレンタル/販売とも実装済み（`ChecksRentableDevices`/`ChecksSaleableDevices`）。Blade 撤去（4-1/4-2）済み。

### 2-2. お手本・共通化のヒント
| 既存ファイル | 内容 |
|-------------|------|
| `frontend/src/components/ui/{SearchBox,Pagination,SummaryCards,FormModal}.tsx` | 共通部品の作り方・配置の見本 |
| `frontend/src/layouts/AuthLayout.tsx` | 認証画面の共通レイアウト（LoginPage が利用）。新しい認証画面はこれで包む |
| `frontend/src/components/ui/Modal.tsx` | 既存の汎用モーダル（`FormModal` の土台。業務モーダルもこれ/FormModal をベースに） |
| `frontend/src/pages/{UsersPage,DeviceCategoriesPage,DeviceFieldsPage}.tsx` | `FormModal` 利用の編集モーダル実装例 |
| `Api\*Controller` + 各 ApiTest | API を足す場合のお手本（admin グループ / 403 / 404 / 422 / reorder） |
| `app/Traits/{ChecksSaleableDevices,ChecksRentableDevices}.php` | FormRequest の `withValidator` で端末状態を検証する trait（販売・レンタルで実装済み） |

→ Blade 撤去（4-1/4-2）が完了し API 専用化。外部依存なしの残タスクは **Phase 4-3（npm 依存整理）/ 4-4（CI）/ 4-6（README・docs）**。
   メール系（3-9・3-1）は仕様確定後に `Mail::fake()` 前提で。

### 2-3. 変更が必要になりうる既存ファイル（タスクにより）
```
frontend/src/components/**      ← 共通部品の新設・切り出し
frontend/src/pages/**           ← 共通部品への置き換え
api/routes/api.php / frontend/src/router.tsx ← メール等で新ルートを足す場合
docs/react-laravel-migration.md ← 対象フェーズの表を更新
```

> ⚠️ ハマりどころ: API FormRequest の `nullable` フィールドは未送信だと `safe()->all()` に**含まれない**。
> `$safe['key'] ?? null` で受けること（カテゴリの icon で 500 になった）。

> ✅ 旧「3 failures（Blade Vite manifest 由来）」は Blade 撤去（Phase 4-1/4-2）で解消済み。
> Laravel は API 専用（`routes/web.php` は Blade UI ルートを持たない）。`resources/views/` は `emails/` のみ残置。

---

## §3. 共通の設計パターン（踏襲すること）

### API（Laravel）
- リスト: `GET /api/xxx` → `{ data: [...], meta: { current_page, last_page, per_page, total } }`
- 詳細: `GET /api/xxx/{id}` → `{ data: {...} }`、未存在は 404 + `{ message }`
- 作成: `POST /api/xxx/store` + 専用 FormRequest → 201 + `{ data, message }`
- CSV: `POST /api/xxx/multi/upload`（解析プレビュー）→ `POST /api/xxx/multi/store`（一括保存）
- バリデーション失敗: 422 + `{ message, errors: { field: [msg] } }`
- 検索: `Keyword` trait の `extractKeywords()`（全角→半角 + スペース区切り AND 検索）
- ルート順: 具体パス（`/search`・`/history`）を `{id}` より**前**に定義
- ⚠️ `devices.lending_now` / `sale_id` は **NOT NULL（デフォルト空文字）**。
  ただし `update()` するモデルは `$fillable` にカラムを含めること（3-7 で `Device::$fillable` に `sale_id` を追加した）。

### フロント（React）
- データ取得: `useQuery`、作成/更新: `useMutation`（`mutateAsync` + try/catch で 422 を捕捉）
- 422 表示: `err.response.data.errors` をフォーム各フィールドに対応付け
- CSV フロー: 3-state component（`upload` → `confirm` → `completed`）
- API クライアント: `import api from '@/lib/api'`（baseURL に `/api` 込み。パスは `/sale` 等）
- 担当者の絞り込み: `useContacts('')` で全件取得 → `client_id` でクライアント側フィルタ
- 端末検索（カート）: `useDeviceSearch(debouncedTerm, '', 1)` を 300ms デバウンスで配線（3-6/3-7 で確立）
- スタイル: 既存 CSS（`rental.css`/`sale.css`）を流用。`.badge`, `.search-pagination` 等は再利用可

---

## §4. API エンドポイント全覧（Phase 3 現在）

| メソッド | パス | 機能 | 状態 |
|---------|------|------|------|
| GET | `/api/dashboard` | ダッシュボード | ✅ |
| GET | `/api/inventory/stocks` | 数量管理 | ✅ |
| GET | `/api/devices/category/{code}` | 個別管理 | ✅ |
| GET | `/api/devices/search` | 端末検索 | ✅ |
| GET | `/api/devices/{id}` | 端末詳細 | ✅ |
| POST | `/api/devices` | 端末登録 | ✅ |
| POST | `/api/devices/multi/{upload,store}` | CSV端末登録 | ✅ |
| GET/POST | `/api/devices/file/{spec,benchmark}` | ファイル管理 | ✅ |
| GET/POST | `/api/clients`, `/api/clients/{id}` | クライアント | ✅ |
| GET/POST | `/api/contacts`, `/api/contacts/{id}` | 担当者 | ✅ |
| GET | `/api/rental` | レンタル一覧 | ✅ |
| POST | `/api/rental/store` | レンタル登録 | ✅ |
| POST | `/api/rental/multi/{upload,store}` | CSV一括レンタル | ✅ |
| POST | `/api/rental/multi/return/{lendId}` | 返却 | ✅ |
| GET | `/api/rental/history{,/{lendId}}` | レンタル履歴 | ✅ |
| GET | `/api/sale` | 販売一覧 | ✅ |
| POST | `/api/sale/store` | 販売登録 | ✅ |
| POST | `/api/sale/multi/{upload,store}` | CSV一括販売 | ✅ |
| GET | `/api/sale/history{,/{saleId}}` | 販売履歴 | ✅ |
| GET | `/api/history?type=&word=&page=` | 統合履歴（レンタル+販売） | ✅ |
| GET/POST | `/api/users`, `PUT /api/users/{id}` | ユーザー管理（admin 限定） | ✅ |
| GET/POST/PUT/DELETE | `/api/device-categories{,/{id}}` + `/reorder` | 機材カテゴリ管理（admin 限定） | ✅ |
| GET/POST/PUT/DELETE | `/api/device-fields{,/{id}}` + `/reorder` | カスタムフィールド管理（admin 限定） | ✅ |

---

## §5. 開始時チェックリスト

```bash
git branch                       # → claude/funny-galileo-6fgy3o
git log --oneline -5             # → 最新が "refactor: Blade UI 撤去（Phase 4-1/4-2）" 系
cd api && composer install       # 依存が無ければ
cd api && php artisan test 2>&1 | grep "Tests:"    # → 344 passed / 1 risky / 0 failures
cd ../frontend && npm ci          # node_modules が無ければ（lockfile は TS 5.9.3 を固定）
cd ../frontend && npm run build && npm run typecheck   # → green
```

> ⚠️ 環境メモ: フレッシュなコンテナでは `api/vendor` と `frontend/node_modules` が未インストールのことがある。
> その場合は `composer install`（api）/ `npm ci`（frontend）を先に実行する。
> `npx tsc` を直接叩くと TS 6.0 系が降ってきて `baseUrl` 廃止エラーが出るが、`npm run` 経由なら lockfile の 5.9.3 が使われ問題なし。

---

## §6. 代替タスク

- **Phase 4-3（npm 依存・Blade ビルド資産の整理）**（難度🟢低・推奨）: 旧 Blade 用 npm 依存／`api/resources/js`・`sass`／mix・vite Blade エントリの整理。削除前に参照ゼロを確認。
- **Phase 4-4（CI 整備）**（難度🟢低）: フロント build/typecheck/lint・API test を CI に追加。
- **プロフィール画面（表示のみ）**（難度🟢低）: `/profile`（`GET /api/auth/me` 表示のみなら外部依存なし）。メール変更は仕様確定後。
- **3-4 残（端末画像）**（難度🟢低・要ストレージ確認）: 端末登録の画像アップロードのみ未実装。
- **3-9 メール/CRM・3-1 認証残**（外部依存・仕様未確定）: メール仕様が決まってから。接続準備（Mailtrap）は済。

---

**ブランチ**: `claude/funny-galileo-6fgy3o`（PR #82 が作成済み。push で更新される）
**次セッション目標**: Phase 4-3（npm/Blade ビルド資産の整理）または 4-4（CI）。いずれも外部依存なし。
メール系（3-9・3-1）は仕様確定後。共通部品・端末状態検証・Blade 撤去は完了済み。
残フェーズ: Phase 4-3〜4-6（npm整理/CI/デプロイ/README）、3-9メール・CRM、3-1 認証残、業務モーダル（対応画面実装時）。
